"""
Service pour parser les fichiers markdown avec codes couleur.
Ce service permet de convertir des fichiers markdown en HTML avec support pour les codes couleur.
"""

import re
import markdown
from typing import Dict, Any, List, Optional, Tuple

class MarkdownParser:
    """Classe pour parser les fichiers markdown avec codes couleur"""
    
    def __init__(self):
        """Initialise le parser markdown"""
        self.color_pattern = re.compile(r'@color\[(.*?)\]\((.*?)\)')
        self.dice_pattern = re.compile(r'@dice\[(.*?)\]')
        self.stat_pattern = re.compile(r'@stat\[(.*?)\]\((.*?)\)')
        self.item_pattern = re.compile(r'@item\[(.*?)\]\((.*?)\)')
        self.npc_pattern = re.compile(r'@npc\[(.*?)\]\((.*?)\)')
        self.monster_pattern = re.compile(r'@monster\[(.*?)\]\((.*?)\)')
        self.secret_pattern = re.compile(r'@secret\[(.*?)\]\((.*?)\)')
        
        # Extensions markdown à utiliser
        self.markdown_extensions = [
            'markdown.extensions.tables',
            'markdown.extensions.fenced_code',
            'markdown.extensions.codehilite',
            'markdown.extensions.nl2br',
            'markdown.extensions.toc'
        ]
    
    def parse_markdown(self, content: str, for_gm: bool = False) -> str:
        """
        Parse le contenu markdown et convertit les codes couleur en HTML.
        
        Args:
            content: Contenu markdown à parser
            for_gm: Si True, inclut le contenu secret pour le MJ
        
        Returns:
            Contenu HTML parsé
        """
        # Remplacer les codes couleur
        content = self.color_pattern.sub(r'<span style="color: \1">\2</span>', content)
        
        # Remplacer les codes de dés
        content = self.dice_pattern.sub(r'<span class="dice">\1</span>', content)
        
        # Remplacer les codes de statistiques
        content = self.stat_pattern.sub(r'<span class="stat" data-stat="\1">\2</span>', content)
        
        # Remplacer les codes d'objets
        content = self.item_pattern.sub(r'<span class="item" data-item="\1">\2</span>', content)
        
        # Remplacer les codes de PNJ
        content = self.npc_pattern.sub(r'<span class="npc" data-npc="\1">\2</span>', content)
        
        # Remplacer les codes de monstres
        content = self.monster_pattern.sub(r'<span class="monster" data-monster="\1">\2</span>', content)
        
        # Traiter les contenus secrets (visibles uniquement pour le MJ)
        if for_gm:
            content = self.secret_pattern.sub(r'<div class="gm-secret">\2</div>', content)
        else:
            content = self.secret_pattern.sub('', content)
        
        # Convertir le markdown en HTML
        html_content = markdown.markdown(content, extensions=self.markdown_extensions)
        
        return html_content
    
    def extract_metadata(self, content: str) -> Tuple[Dict[str, Any], str]:
        """
        Extrait les métadonnées YAML du début du fichier markdown.
        
        Args:
            content: Contenu markdown avec métadonnées YAML
        
        Returns:
            Tuple contenant les métadonnées et le contenu sans les métadonnées
        """
        import yaml
        
        # Vérifier si le contenu commence par des métadonnées YAML (---)
        if content.startswith('---'):
            # Trouver la fin des métadonnées
            end_index = content.find('---', 3)
            if end_index != -1:
                # Extraire les métadonnées
                yaml_content = content[3:end_index].strip()
                try:
                    metadata = yaml.safe_load(yaml_content)
                    # Extraire le contenu sans les métadonnées
                    content_without_metadata = content[end_index + 3:].strip()
                    return metadata, content_without_metadata
                except yaml.YAMLError:
                    # En cas d'erreur de parsing YAML
                    pass
        
        # Si pas de métadonnées ou erreur de parsing
        return {}, content
    
    def parse_markdown_file(self, file_path: str, for_gm: bool = False) -> Tuple[Dict[str, Any], str]:
        """
        Parse un fichier markdown et extrait les métadonnées.
        
        Args:
            file_path: Chemin vers le fichier markdown
            for_gm: Si True, inclut le contenu secret pour le MJ
        
        Returns:
            Tuple contenant les métadonnées et le contenu HTML parsé
        """
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Extraire les métadonnées
            metadata, content_without_metadata = self.extract_metadata(content)
            
            # Parser le contenu markdown
            html_content = self.parse_markdown(content_without_metadata, for_gm)
            
            return metadata, html_content
        except Exception as e:
            print(f"Erreur lors du parsing du fichier markdown: {e}")
            return {}, f"<p>Erreur lors du parsing du fichier: {str(e)}</p>"
    
    def generate_toc(self, content: str) -> str:
        """
        Génère une table des matières à partir du contenu markdown.
        
        Args:
            content: Contenu markdown
        
        Returns:
            Table des matières au format HTML
        """
        # Extraire les titres
        headers = re.findall(r'^(#{1,6})\s+(.+?)$', content, re.MULTILINE)
        
        if not headers:
            return ""
        
        toc = "<div class='toc'>\n<h3>Table des matières</h3>\n<ul>\n"
        
        for header in headers:
            level = len(header[0])
            title = header[1]
            anchor = title.lower().replace(' ', '-')
            
            # Indentation en fonction du niveau
            indent = "  " * (level - 1)
            toc += f"{indent}<li><a href='#{anchor}'>{title}</a></li>\n"
        
        toc += "</ul>\n</div>"
        
        return toc
    
    def add_anchors_to_headers(self, content: str) -> str:
        """
        Ajoute des ancres aux titres pour la navigation.
        
        Args:
            content: Contenu markdown
        
        Returns:
            Contenu markdown avec ancres
        """
        def replace_header(match):
            hashes, title = match.groups()
            anchor = title.lower().replace(' ', '-')
            return f"{hashes} {title} <a id='{anchor}'></a>"
        
        return re.sub(r'^(#{1,6})\s+(.+?)$', replace_header, content, flags=re.MULTILINE)
    
    def parse_scenario_file(self, file_path: str) -> Dict[str, Any]:
        """
        Parse un fichier de scénario markdown et le convertit en structure de données.
        
        Args:
            file_path: Chemin vers le fichier de scénario
        
        Returns:
            Dictionnaire contenant les données du scénario
        """
        try:
            metadata, html_content = self.parse_markdown_file(file_path, for_gm=True)
            
            # Extraire les sections du scénario
            sections = self._extract_scenario_sections(file_path)
            
            # Construire la structure de données du scénario
            scenario_data = {
                "metadata": metadata,
                "html_content": html_content,
                "sections": sections
            }
            
            return scenario_data
        except Exception as e:
            print(f"Erreur lors du parsing du fichier de scénario: {e}")
            return {"error": str(e)}
    
    def _extract_scenario_sections(self, file_path: str) -> List[Dict[str, Any]]:
        """
        Extrait les sections d'un fichier de scénario.
        
        Args:
            file_path: Chemin vers le fichier de scénario
        
        Returns:
            Liste des sections du scénario
        """
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Ignorer les métadonnées YAML
            if content.startswith('---'):
                end_index = content.find('---', 3)
                if end_index != -1:
                    content = content[end_index + 3:].strip()
            
            # Extraire les sections (titres de niveau 2)
            section_pattern = re.compile(r'^##\s+(.+?)$(.*?)(?=^##\s+|\Z)', re.MULTILINE | re.DOTALL)
            sections = []
            
            for match in section_pattern.finditer(content):
                title = match.group(1).strip()
                content = match.group(2).strip()
                
                # Parser le contenu de la section
                html_content = self.parse_markdown(content, for_gm=True)
                
                # Extraire les sous-sections (titres de niveau 3)
                subsection_pattern = re.compile(r'^###\s+(.+?)$(.*?)(?=^###\s+|\Z)', re.MULTILINE | re.DOTALL)
                subsections = []
                
                for submatch in subsection_pattern.finditer(content):
                    subtitle = submatch.group(1).strip()
                    subcontent = submatch.group(2).strip()
                    
                    # Parser le contenu de la sous-section
                    sub_html_content = self.parse_markdown(subcontent, for_gm=True)
                    
                    subsections.append({
                        "title": subtitle,
                        "content": subcontent,
                        "html_content": sub_html_content
                    })
                
                sections.append({
                    "title": title,
                    "content": content,
                    "html_content": html_content,
                    "subsections": subsections
                })
            
            return sections
        except Exception as e:
            print(f"Erreur lors de l'extraction des sections: {e}")
            return []

# Créer une instance du parser
markdown_parser = MarkdownParser()
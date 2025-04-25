import pytest
from unittest.mock import MagicMock
import pytest_asyncio
from sqlalchemy import func, select

from app.services.joueur_service import JoueurService
from app.schemas.joueur import JoueurCreate, JoueurLogin
from app.db.models.joueur import Joueur
from app.core.security import hash_password, verify_password, create_tokens

@pytest.fixture
def joueur_service(async_session):
    """Fixture pour une instance de JoueurService avec une session de test."""
    return JoueurService(db=async_session)

# Grouper les tests par méthode testée
class TestCreateJoueur:
    @pytest.mark.asyncio
    async def test_create_joueur_success(self, joueur_service, async_session, monkeypatch):
        """Teste la création réussie d'un joueur."""
        joueur_data = JoueurCreate(email="test@example.com", username="testuser", password="password123")

        # Mock des fonctions de sécurité
        mock_hash = MagicMock(return_value="hashed_password")
        monkeypatch.setattr("app.services.joueur_service.hash_password", mock_hash)

        created_joueur = await joueur_service.create_joueur(joueur_data)

        assert created_joueur is not None
        assert created_joueur.email == joueur_data.email
        assert created_joueur.username == joueur_data.username
        assert created_joueur.hashed_password == "hashed_password"
        assert not created_joueur.email_confirmed
        assert created_joueur.id is not None # L'ID devrait être attribué après le commit et refresh

        # Vérifier que le joueur a bien été ajouté à la base de données
        retrieved_joueur = await async_session.get(Joueur, created_joueur.id)
        assert retrieved_joueur is not None
        assert retrieved_joueur.email == joueur_data.email

        mock_hash.assert_called_once_with(joueur_data.password)

    @pytest.mark.asyncio
    async def test_create_joueur_already_exists(self, joueur_service, async_session):
        """Teste la création d'un joueur qui existe déjà."""
        # Créez un joueur existant directement dans la base de données de test
        existing_joueur = Joueur(email="existing@example.com", username="existinguser", hashed_password="hashed_password")
        async_session.add(existing_joueur)
        await async_session.commit()

        joueur_data = JoueurCreate(email="existing@example.com", username="anotheruser", password="password123")

        created_joueur = await joueur_service.create_joueur(joueur_data)

        assert created_joueur is None

        # Vérifiez qu'aucun nouveau joueur n'a été ajouté
        count = await async_session.scalar(select(func.count()).select_from(Joueur))
        assert count == 1 # Seul le joueur existant devrait être présent


class TestAuthenticateJoueur:
    @pytest.mark.asyncio
    async def test_authenticate_joueur_success(self, joueur_service, async_session, monkeypatch):
        """Teste l'authentification réussie d'un joueur actif."""
        login_data = JoueurLogin(email="test@example.com", password="password123")
        hashed_password = "hashed_password"

        # Mock des fonctions de sécurité
        mock_verify = MagicMock(return_value=True)
        monkeypatch.setattr("app.services.joueur_service.verify_password", mock_verify)

        # Créez un joueur dans la base de données de test
        joueur = Joueur(
            email="test@example.com", 
            username="testuser", 
            hashed_password=hashed_password, 
            is_active=True, 
            is_banned=False
        )
        async_session.add(joueur)
        await async_session.commit()

        authenticated_joueur = await joueur_service.authenticate_joueur(login_data)

        assert authenticated_joueur is not None
        assert authenticated_joueur.email == login_data.email
        mock_verify.assert_called_once_with(login_data.password, hashed_password)

    @pytest.mark.asyncio
    async def test_authenticate_joueur_invalid_credentials(self, joueur_service, async_session, monkeypatch):
        """Teste l'authentification avec des identifiants invalides."""
        login_data = JoueurLogin(email="test@example.com", password="wrongpassword")
        hashed_password = "hashed_password"

        # Mock des fonctions de sécurité
        mock_verify = MagicMock(return_value=False)
        monkeypatch.setattr("app.services.joueur_service.verify_password", mock_verify)

        # Créez un joueur dans la base de données de test
        joueur = Joueur(
            email="test@example.com", 
            username="testuser", 
            hashed_password=hashed_password, 
            is_active=True, 
            is_banned=False
        )
        async_session.add(joueur)
        await async_session.commit()

        authenticated_joueur = await joueur_service.authenticate_joueur(login_data)

        assert authenticated_joueur is None
        mock_verify.assert_called_once_with(login_data.password, hashed_password)

    @pytest.mark.asyncio
    async def test_authenticate_joueur_not_found(self, joueur_service, async_session):
        """Teste l'authentification d'un joueur qui n'existe pas."""
        login_data = JoueurLogin(email="nonexistent@example.com", password="password123")

        authenticated_joueur = await joueur_service.authenticate_joueur(login_data)

        assert authenticated_joueur is None

    @pytest.mark.asyncio
    async def test_authenticate_joueur_inactive(self, joueur_service, async_session, monkeypatch):
        """Teste l'authentification d'un joueur inactif."""
        login_data = JoueurLogin(email="inactive@example.com", password="password123")
        hashed_password = "hashed_password"

        # Mock des fonctions de sécurité
        mock_verify = MagicMock(return_value=True)
        monkeypatch.setattr("app.services.joueur_service.verify_password", mock_verify)

        # Créez un joueur inactif dans la base de données de test
        joueur = Joueur(
            email="inactive@example.com", 
            username="inactiveuser", 
            hashed_password=hashed_password, 
            is_active=False, 
            is_banned=False
        )
        async_session.add(joueur)
        await async_session.commit()

        authenticated_joueur = await joueur_service.authenticate_joueur(login_data)

        assert authenticated_joueur is None
        mock_verify.assert_called_once_with(login_data.password, hashed_password)

    @pytest.mark.asyncio
    async def test_authenticate_joueur_banned(self, joueur_service, async_session, monkeypatch):
        """Teste l'authentification d'un joueur banni."""
        login_data = JoueurLogin(email="banned@example.com", password="password123")
        hashed_password = "hashed_password"

        # Mock des fonctions de sécurité
        mock_verify = MagicMock(return_value=True)
        monkeypatch.setattr("app.services.joueur_service.verify_password", mock_verify)

        # Créez un joueur banni dans la base de données de test
        joueur = Joueur(
            email="banned@example.com", 
            username="banneduser", 
            hashed_password=hashed_password, 
            is_active=True, 
            is_banned=True
        )
        async_session.add(joueur)
        await async_session.commit()

        authenticated_joueur = await joueur_service.authenticate_joueur(login_data)

        assert authenticated_joueur is None
        mock_verify.assert_called_once_with(login_data.password, hashed_password)


class TestUpdateLastLogin:
    @pytest.mark.asyncio
    async def test_update_last_login(self, joueur_service, async_session):
        """Teste la mise à jour de la dernière connexion."""
        # Créez un joueur dans la base de données de test
        joueur = Joueur(email="test@example.com", username="testuser", hashed_password="hashed_password")
        async_session.add(joueur)
        await async_session.commit()
        await async_session.refresh(joueur) # Rafraîchir pour obtenir l'ID

        ip = "192.168.1.1"
        ua = "TestBrowser"

        await joueur_service.update_last_login(joueur, ip, ua)

        # Récupérer le joueur depuis la base de données pour vérifier les mises à jour
        updated_joueur = await async_session.get(Joueur, joueur.id)

        assert updated_joueur is not None
        assert updated_joueur.last_login_ip == ip
        assert updated_joueur.last_login_ua == ua


class TestCreateAuthTokens:
    def test_create_auth_tokens(self, joueur_service, monkeypatch):
        """Teste la création de tokens d'authentification."""
        joueur_id = 1
        
        # Mock des fonctions de sécurité
        mock_create_tokens = MagicMock(return_value=("access_token", "refresh_token"))
        monkeypatch.setattr("app.services.joueur_service.create_tokens", mock_create_tokens)

        access_token, refresh_token = joueur_service.create_auth_tokens(joueur_id)

        assert access_token == "access_token"
        assert refresh_token == "refresh_token"
        mock_create_tokens.assert_called_once_with({"sub": str(joueur_id)})
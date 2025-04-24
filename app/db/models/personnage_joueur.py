# app/db/models/personnage_joueur.py

from sqlalchemy import Column, Integer, String, ForeignKey, DateTime, JSON
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from app.db.base import Base

class Personnage(Base):
    __tablename__ = "personnages"

    id = Column(Integer, primary_key=True)
    joueur_id = Column(Integer, ForeignKey("joueurs.id", ondelete="CASCADE"), nullable=False)

    nom = Column(String, nullable=False)
    classe = Column(String, nullable=False)
    race = Column(String, default="Humain")
    alignement = Column(String, nullable=False)
    niveau = Column(Integer, default=1)
    experience = Column(Integer, default=0)

    stats = Column(JSON, nullable=False)  # Caractéristiques + mods
    sauvegardes = Column(JSON, nullable=False)

    points_de_vie = Column(Integer, nullable=False)
    points_de_vie_max = Column(Integer, nullable=False)
    des_de_vie = Column(String)
    classe_armure = Column(Integer)
    thac0 = Column(Integer)

    deplacement_base = Column(Integer)
    deplacement_rencontre = Column(Integer)

    sorts_memorises = Column(JSON, default=[])
    livre_de_sorts = Column(JSON, default=[])

    or_possede = Column(Integer, default=0)
    monnaie = Column(JSON, default={})
    equipement = Column(JSON, default=[])
    objets_magiques = Column(JSON, default=[])

    langues_connues = Column(JSON, default=[])
    suivants = Column(JSON, default=[])
    domaine = Column(String, nullable=True)

    date_creation = Column(DateTime(timezone=True), server_default=func.now())

    joueur = relationship("Joueur", back_populates="personnages")

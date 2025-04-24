from sqlalchemy import Column, DateTime, ForeignKey, Integer, String
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from app.db.base import Base

class ConnexionJoueur(Base):
    __tablename__ = "connexions_joueur"

    id = Column(Integer, primary_key=True)
    joueur_id = Column(Integer, ForeignKey("joueurs.id", ondelete="CASCADE"))
    ip = Column(String, nullable=True)
    user_agent = Column(String, nullable=True)
    timestamp = Column(DateTime(timezone=True), server_default=func.now())

    joueur = relationship("Joueur", back_populates="connexions")

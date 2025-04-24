from sqlalchemy import Boolean, Column, DateTime, Integer, String, UniqueConstraint
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from app.db.base import Base

class Joueur(Base):
    __tablename__ = "joueurs"
    __table_args__ = (
        UniqueConstraint("username", name="uq_joueur_username"),
        UniqueConstraint("email", name="uq_joueur_email"),
    )

    id = Column(Integer, primary_key=True, index=True)
    email = Column(String(255), unique=True, nullable=False)
    username = Column(String(32), unique=True, nullable=False)

    hashed_password = Column(String, nullable=False)

    is_active = Column(Boolean, default=True)
    is_banned = Column(Boolean, default=False)
    email_confirmed = Column(Boolean, default=False)

    last_login_ip = Column(String, nullable=True)
    last_login_ua = Column(String, nullable=True)

    verification_required = Column(Boolean, default=False)

    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    connexions = relationship("ConnexionJoueur", back_populates="joueur", cascade="all, delete-orphan")
    personnages = relationship("Personnage", back_populates="joueur", cascade="all, delete-orphan")

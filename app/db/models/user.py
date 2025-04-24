from sqlalchemy import Boolean, Column, Integer, String, DateTime
from sqlalchemy.sql import func
from app.db.base import Base

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    email = Column(String, unique=True, nullable=False, index=True)
    password_hash = Column(String, nullable=False)
    credit_balance = Column(Integer, default=0)
    is_admin = Column(Boolean, default=False)
    is_banned = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

from sqlalchemy import Column, Integer, String, ForeignKey, DateTime
from sqlalchemy.sql import func
from app.db.base import Base

class CreditTransaction(Base):
    __tablename__ = "credit_transactions"

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    type = Column(String)  # "recharge", "usage", etc.
    amount = Column(Integer)
    comment = Column(String, nullable=True)
    timestamp = Column(DateTime(timezone=True), server_default=func.now())

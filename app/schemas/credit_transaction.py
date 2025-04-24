from pydantic import BaseModel
from datetime import datetime

class CreditTransactionOut(BaseModel):
    id: int
    type: str
    amount: int
    comment: str | None
    timestamp: datetime

    class Config:
        from_attributes = True

class GrantCreditRequest(BaseModel):
    user_id: int
    amount: int
    comment: str = "Crédit attribué par un administrateur"

class RechargeRequest(BaseModel):
    amount: int
    comment: str = "Recharge utilisateur"

class UseCreditsRequest(BaseModel):
    amount: int
    comment: str = "Utilisation de crédits"

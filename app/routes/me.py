from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.dependencies.auth import get_current_user
from app.core.database import get_db
from app.schemas.credit_transaction import CreditTransactionOut
from app.schemas.credit_transaction import RechargeRequest, UseCreditsRequest
from app.services.credit_service import CreditService # Import du service
from app.db.models import User # Nécessaire pour le type hint de current_user

router = APIRouter(prefix="/me", tags=["Me"])

# Dépendance pour obtenir le CreditService
async def get_credit_service(db: AsyncSession = Depends(get_db)) -> CreditService:
    return CreditService(db)

@router.get("/credits")
async def get_my_credits(
    current_user: User = Depends(get_current_user),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    balance = await credit_service.get_credit_balance(current_user)
    return {"credit_balance": balance}

@router.get("/transactions", response_model=list[CreditTransactionOut])
async def get_my_transactions(
    current_user: User = Depends(get_current_user),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    return await credit_service.list_transactions_by_user_id(current_user.id)


@router.post("/credits/recharge")
async def recharge_credits(
    data: RechargeRequest,
    current_user: User = Depends(get_current_user),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    if data.amount <= 0:
        raise HTTPException(status_code=400, detail="Montant invalide")

    new_balance = await credit_service.recharge_credits(current_user, data)
    return {
        "msg": "Crédits ajoutés",
        "new_balance": new_balance
    }


@router.post("/credits/use")
async def use_credits(
    data: UseCreditsRequest,
    current_user: User = Depends(get_current_user),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    if data.amount <= 0:
        raise HTTPException(status_code=400, detail="Montant invalide")

    new_balance = await credit_service.use_credits(current_user, data)
    if new_balance == -1: # Vérifie la valeur de retour indiquant crédits insuffisants
         raise HTTPException(status_code=402, detail="Crédits insuffisants")

    return {
        "msg": "Crédits consommés",
        "new_balance": new_balance
    }

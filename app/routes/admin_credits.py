from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.dependencies.auth import require_admin
from app.core.database import get_db
from app.schemas.credit_transaction import GrantCreditRequest, CreditTransactionOut
from app.schemas.user import UserOut # Pour le retour de list_users_with_credits
from app.services.credit_service import CreditService # Import du service

router = APIRouter(prefix="/admin/credits", tags=["Admin Credits"])

# Dépendance pour obtenir le CreditService
async def get_credit_service(db: AsyncSession = Depends(get_db)) -> CreditService:
    return CreditService(db)

@router.post("/grant")
async def grant_credits(
    data: GrantCreditRequest,
    admin=Depends(require_admin),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    user = await credit_service.grant_credits(data.user_id, data)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")

    return {"credit_balance": user.credit_balance}

@router.get("/users", response_model=list[UserOut]) # Utilisation de UserOut pour le retour
async def list_users_with_credits(
    admin=Depends(require_admin),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    users = await credit_service.list_users_with_credits()
    # Mapper les objets User en UserOut si nécessaire, ou ajuster le service pour retourner UserOut
    # Pour l'instant, on retourne les objets User directement si le schéma UserOut correspond
    return users

@router.get("/user/{user_id}") # Le response_model peut être défini ici si un schéma spécifique est créé
async def user_credit_info(
    user_id: int,
    admin=Depends(require_admin),
    credit_service: CreditService = Depends(get_credit_service) # Injection du service
):
    result = await credit_service.get_user_credit_info(user_id)
    if not result:
        raise HTTPException(status_code=404, detail="User not found")

    user, transactions = result

    return {
        "user": {
            "id": user.id,
            "email": user.email,
            "credit_balance": user.credit_balance
        },
        "transactions": transactions
    }

from fastapi import APIRouter, Depends
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from app.dependencies.auth import get_current_user
from app.core.database import get_db
from app.db.models.credit_transaction import CreditTransaction
from app.schemas.credit_transaction import CreditTransactionOut
from app.schemas.credit_transaction import RechargeRequest, UseCreditsRequest

router = APIRouter(prefix="/me", tags=["Me"])

@router.get("/credits")
async def get_my_credits(current_user=Depends(get_current_user)):
    return {"credit_balance": current_user.credit_balance}

@router.get("/transactions", response_model=list[CreditTransactionOut])
async def get_my_transactions(
    current_user=Depends(get_current_user),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(
        select(CreditTransaction)
        .where(CreditTransaction.user_id == current_user.id)
        .order_by(CreditTransaction.timestamp.desc())
    )
    return result.scalars().all()


@router.post("/credits/recharge")
async def recharge_credits(
    data: RechargeRequest,
    current_user=Depends(get_current_user),
    db: AsyncSession = Depends(get_db)
):
    if data.amount <= 0:
        raise HTTPException(status_code=400, detail="Montant invalide")

    current_user.credit_balance += data.amount
    tx = CreditTransaction(
        user_id=current_user.id,
        amount=data.amount,
        type="recharge",
        comment=data.comment
    )
    db.add(tx)
    await db.commit()
    return {
        "msg": "Crédits ajoutés",
        "new_balance": current_user.credit_balance
    }


@router.post("/credits/use")
async def use_credits(
    data: UseCreditsRequest,
    current_user=Depends(get_current_user),
    db: AsyncSession = Depends(get_db)
):
    if data.amount <= 0:
        raise HTTPException(status_code=400, detail="Montant invalide")
    if current_user.credit_balance < data.amount:
        raise HTTPException(status_code=402, detail="Crédits insuffisants")

    current_user.credit_balance -= data.amount
    tx = CreditTransaction(
        user_id=current_user.id,
        amount=-data.amount,
        type="usage",
        comment=data.comment
    )
    db.add(tx)
    await db.commit()
    return {
        "msg": "Crédits consommés",
        "new_balance": current_user.credit_balance
    }

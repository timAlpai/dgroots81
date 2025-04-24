from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from app.dependencies.auth import require_admin
from app.core.database import get_db
from app.db.models.user import User
from app.db.models.credit_transaction import CreditTransaction
from app.schemas.credit_transaction import GrantCreditRequest

router = APIRouter(prefix="/admin/credits", tags=["Admin Credits"])

@router.post("/grant")
async def grant_credits(
    data: GrantCreditRequest,
    admin=Depends(require_admin),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(User).where(User.id == data.user_id))
    user = result.scalar_one_or_none()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")

    user.credit_balance += data.amount
    transaction = CreditTransaction(
        user_id=user.id,
        amount=data.amount,
        type="recharge",
        comment=data.comment
    )
    db.add(transaction)
    await db.commit()
    return {"credit_balance": user.credit_balance}

@router.get("/users")
async def list_users_with_credits(
    admin=Depends(require_admin),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(User))
    return [
        {"id": u.id, "email": u.email, "credit_balance": u.credit_balance}
        for u in result.scalars().all()
    ]

@router.get("/user/{user_id}")
async def user_credit_info(
    user_id: int,
    admin=Depends(require_admin),
    db: AsyncSession = Depends(get_db)
):
    user = (await db.execute(select(User).where(User.id == user_id))).scalar_one_or_none()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")

    tx = (await db.execute(
        select(CreditTransaction)
        .where(CreditTransaction.user_id == user_id)
        .order_by(CreditTransaction.timestamp.desc())
    )).scalars().all()

    return {
        "user": {
            "id": user.id,
            "email": user.email,
            "credit_balance": user.credit_balance
        },
        "transactions": tx
    }

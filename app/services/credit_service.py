# app/services/credit_service.py

from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import List, Optional # Ajout de Optional

from app.db.models import User, CreditTransaction
from app.schemas.credit_transaction import RechargeRequest, UseCreditsRequest, CreditTransactionOut, GrantCreditRequest # Ajout de GrantCreditRequest
from app.schemas.user import UserOut # Pour le retour de list_users_with_credits

class CreditService:
    def __init__(self, db: AsyncSession):
        self.db = db

    async def get_credit_balance(self, user: User) -> int:
        # Le solde est directement disponible sur l'objet User
        return user.credit_balance

    async def list_transactions_by_user_id(self, user_id: int) -> List[CreditTransaction]:
        result = await self.db.execute(
            select(CreditTransaction)
            .where(CreditTransaction.user_id == user_id)
            .order_by(CreditTransaction.timestamp.desc())
        )
        return result.scalars().all()

    async def recharge_credits(self, user: User, data: RechargeRequest) -> int:
        if data.amount <= 0:
            # La validation du montant peut rester dans la route ou être déplacée ici
            # Pour l'instant, je la laisse dans la route et suppose un montant valide ici
            pass # TODO: Ajouter une gestion d'erreur ou une validation plus poussée si nécessaire

        user.credit_balance += data.amount
        tx = CreditTransaction(
            user_id=user.id,
            amount=data.amount,
            type="recharge",
            comment=data.comment
        )
        self.db.add(tx)
        await self.db.commit()
        await self.db.refresh(user) # Rafraîchir l'utilisateur pour le nouveau solde
        return user.credit_balance

    async def use_credits(self, user: User, data: UseCreditsRequest) -> int:
        if data.amount <= 0:
             # TODO: Ajouter une gestion d'erreur ou une validation plus poussée si nécessaire
             pass

        if user.credit_balance < data.amount:
            return -1 # Indique crédits insuffisants

        user.credit_balance -= data.amount
        tx = CreditTransaction(
            user_id=user.id,
            amount=-data.amount,
            type="usage",
            comment=data.comment
        )
        self.db.add(tx)
        await self.db.commit()
        await self.db.refresh(user) # Rafraîchir l'utilisateur pour le nouveau solde
        return user.credit_balance

    # Méthodes d'administration des crédits
    async def grant_credits(self, user_id: int, data: GrantCreditRequest) -> Optional[User]:
        result = await self.db.execute(select(User).where(User.id == user_id))
        user = result.scalar_one_or_none()
        if not user:
            return None # Utilisateur introuvable

        user.credit_balance += data.amount
        transaction = CreditTransaction(
            user_id=user.id,
            amount=data.amount,
            type="recharge", # Ou un nouveau type "grant" si nécessaire
            comment=data.comment
        )
        self.db.add(transaction)
        await self.db.commit()
        await self.db.refresh(user)
        return user

    async def list_users_with_credits(self) -> List[User]:
        result = await self.db.execute(select(User))
        return result.scalars().all()

    async def get_user_credit_info(self, user_id: int) -> Optional[tuple[User, List[CreditTransaction]]]:
        user = (await self.db.execute(select(User).where(User.id == user_id))).scalar_one_or_none()
        if not user:
            return None

        tx = (await self.db.execute(
            select(CreditTransaction)
            .where(CreditTransaction.user_id == user_id)
            .order_by(CreditTransaction.timestamp.desc())
        )).scalars().all()

        return user, tx
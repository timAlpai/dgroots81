from fastapi import Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from app.db.models import Joueur, Personnage
from app.dependencies.joueur import get_current_joueur
from app.core.database import get_db

async def is_admin_or_mj_for_personnage(
    personnage_id: int,
    current_joueur: Joueur = Depends(get_current_joueur),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(
        select(Personnage).where(Personnage.id == personnage_id)
    )
    personnage = result.scalar_one_or_none()
    if not personnage:
        raise HTTPException(status_code=404, detail="Personnage non trouvé")

    # Vérification : proprio ou joueur admin ou MJ
    if personnage.joueur_id != current_joueur.id and not current_joueur.is_admin:
        raise HTTPException(status_code=403, detail="Accès refusé à ce personnage")

    return personnage

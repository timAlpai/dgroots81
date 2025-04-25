from fastapi import APIRouter
from app.routes import auth, admin, me, admin_credits, joueur, personnage, jeu

router = APIRouter()

# Routes API
router.include_router(auth.router)
router.include_router(admin.router)
router.include_router(me.router)
router.include_router(admin_credits.router)
router.include_router(joueur.router)
router.include_router(personnage.router)
router.include_router(jeu.router)
from fastapi import APIRouter, Depends
from app.dependencies.auth import require_admin

router = APIRouter(prefix="/admin", tags=["Admin"])

@router.get("/ping")
async def admin_ping(user=Depends(require_admin)):
    return {"msg": f"Hello admin {user.email}"}

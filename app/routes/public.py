from fastapi import APIRouter

router = APIRouter()

@router.get("/")
def ping():
    return {"status": "ok"}

@router.get("/health")
async def health_check():
    return {"status": "healthy"}
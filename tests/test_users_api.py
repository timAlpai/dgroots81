import pytest
from fastapi.testclient import TestClient
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.user import User


@pytest.mark.asyncio
async def test_read_users(client: TestClient, test_admin_token: str, test_token: str):
    """Test de l'endpoint GET /users/"""
    # Test avec un token d'administrateur
    response = client.get(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"}
    )
    
    assert response.status_code == 200
    users = response.json()
    assert isinstance(users, list)
    assert len(users) > 0
    
    # Test avec un token utilisateur normal (non autorisé)
    response = client.get(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 403
    
    # Test sans token
    response = client.get("/api/users/")
    assert response.status_code == 401


@pytest.mark.asyncio
async def test_create_user(client: TestClient, test_admin_token: str, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint POST /users/"""
    # Test avec un token d'administrateur
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "newuser",
            "email": "newuser@example.com",
            "password": "newpassword123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    assert response.status_code == 200
    user_data = response.json()
    assert user_data["username"] == "newuser"
    assert user_data["email"] == "newuser@example.com"
    assert "id" in user_data
    assert "hashed_password" not in user_data
    
    # Créer un utilisateur pour tester la contrainte d'unicité
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "uniqueuser",
            "email": "unique@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    assert response.status_code == 200
    
    # Test avec un nom d'utilisateur déjà utilisé
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "uniqueuser",  # Nom d'utilisateur déjà utilisé
            "email": "another@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    assert response.status_code == 400
    assert "detail" in response.json()
    
    # Test avec un email déjà utilisé
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "anotheruser",
            "email": "unique@example.com",  # Email déjà utilisé
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    assert response.status_code == 400
    assert "detail" in response.json()
    
    # Test avec un token utilisateur normal (non autorisé)
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "username": "unauthorizeduser",
            "email": "unauthorized@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    assert response.status_code == 403


@pytest.mark.asyncio
async def test_read_user(client: TestClient, test_user: User, test_token: str, test_admin_token: str):
    """Test de l'endpoint GET /users/{user_id}"""
    # Test avec un utilisateur qui consulte son propre profil
    response = client.get(
        f"/api/users/{test_user.id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 200
    user_data = response.json()
    assert user_data["id"] == test_user.id
    assert user_data["username"] == test_user.username
    assert user_data["email"] == test_user.email
    assert "average_session_time" in user_data
    
    # Test avec un administrateur qui consulte le profil d'un autre utilisateur
    response = client.get(
        f"/api/users/{test_user.id}",
        headers={"Authorization": f"Bearer {test_admin_token}"}
    )
    
    assert response.status_code == 200
    
    # Test avec un utilisateur normal qui tente de consulter le profil d'un autre utilisateur
    # Créer d'abord un autre utilisateur
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "anotheruser",
            "email": "another@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    another_user_id = response.json()["id"]
    
    # Tenter d'accéder au profil de cet utilisateur
    response = client.get(
        f"/api/users/{another_user_id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 403
    
    # Test avec un ID utilisateur inexistant
    response = client.get(
        "/api/users/9999",
        headers={"Authorization": f"Bearer {test_admin_token}"}
    )
    
    assert response.status_code == 404


@pytest.mark.asyncio
async def test_update_user(client: TestClient, test_user: User, test_token: str, test_admin_token: str):
    """Test de l'endpoint PUT /users/{user_id}"""
    # Test avec un utilisateur qui met à jour son propre profil
    response = client.put(
        f"/api/users/{test_user.id}",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "username": "updateduser",
            "email": "updated@example.com",
            "password": "newpassword123"
        }
    )
    
    assert response.status_code == 200
    user_data = response.json()
    assert user_data["username"] == "updateduser"
    assert user_data["email"] == "updated@example.com"
    
    # Test avec un administrateur qui met à jour le profil d'un autre utilisateur
    # Créer d'abord un autre utilisateur
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "usertobemodified",
            "email": "tobemodified@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    another_user_id = response.json()["id"]
    
    # Mettre à jour cet utilisateur
    response = client.put(
        f"/api/users/{another_user_id}",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "modifieduser",
            "email": "modified@example.com",
            "is_active": False
        }
    )
    
    assert response.status_code == 200
    user_data = response.json()
    assert user_data["username"] == "modifieduser"
    assert user_data["email"] == "modified@example.com"
    assert user_data["is_active"] is False
    
    # Test avec un utilisateur normal qui tente de mettre à jour le profil d'un autre utilisateur
    response = client.put(
        f"/api/users/{another_user_id}",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "username": "unauthorizedupdate",
            "email": "unauthorized@example.com"
        }
    )
    
    assert response.status_code == 403
    
    # Test avec un ID utilisateur inexistant
    response = client.put(
        "/api/users/9999",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "nonexistentuser",
            "email": "nonexistent@example.com"
        }
    )
    
    assert response.status_code == 404
    
    # Créer un utilisateur avec un nom d'utilisateur unique pour tester la contrainte d'unicité
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "uniqueusername",
            "email": "uniqueemail@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    # Test avec un nom d'utilisateur déjà utilisé
    response = client.put(
        f"/api/users/{test_user.id}",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "username": "uniqueusername"  # Nom d'utilisateur déjà utilisé
        }
    )
    
    assert response.status_code == 400
    assert "detail" in response.json()


@pytest.mark.asyncio
async def test_delete_user(client: TestClient, test_user: User, test_token: str, test_admin_token: str):
    """Test de l'endpoint DELETE /users/{user_id}"""
    # Créer d'abord un utilisateur à supprimer
    response = client.post(
        "/api/users/",
        headers={"Authorization": f"Bearer {test_admin_token}"},
        json={
            "username": "usertodelete",
            "email": "todelete@example.com",
            "password": "password123",
            "is_active": True,
            "is_superuser": False
        }
    )
    
    user_to_delete_id = response.json()["id"]
    
    # Test avec un utilisateur normal (non autorisé)
    response = client.delete(
        f"/api/users/{user_to_delete_id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 403
    
    # Test avec un administrateur
    response = client.delete(
        f"/api/users/{user_to_delete_id}",
        headers={"Authorization": f"Bearer {test_admin_token}"}
    )
    
    assert response.status_code == 204
    
    # Vérifier que l'utilisateur a été supprimé
    response = client.get(
        f"/api/users/{user_to_delete_id}",
        headers={"Authorization": f"Bearer {test_admin_token}"}
    )
    
    assert response.status_code == 404
    
    # Test avec un ID utilisateur inexistant
    response = client.delete(
        "/api/users/9999",
        headers={"Authorization": f"Bearer {test_admin_token}"}
    )
    
    assert response.status_code == 404
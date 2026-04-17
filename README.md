# 🚀 SymfoConnect

SymfoConnect est une application web développée avec Symfony simulant un réseau social moderne.

Elle permet aux utilisateurs de :
- publier des posts
- suivre d'autres utilisateurs
- liker et commenter des publications
- échanger des messages privés
- recevoir des notifications
- interagir via une API REST

---

## 🧱 Stack technique

- **Backend** : Symfony 6 / 7
- **Base de données** : MySQL / SQLite (tests)
- **ORM** : Doctrine
- **API** : API Platform
- **Asynchrone** : Symfony Messenger
- **Tests** : PHPUnit
- **Frontend** : Twig + CSS

---

## 📦 Fonctionnalités principales

### 👤 Utilisateurs
- Inscription / Connexion
- Profil utilisateur
- Suivi d’autres utilisateurs (follow / unfollow)

### 📝 Posts
- Création de posts
- Affichage du fil d’actualité
- Likes
- Commentaires

### 💬 Messagerie
- Envoi de messages privés
- Conversations entre utilisateurs
- Notifications

### ⚡ Performances
- Cache du fil d’actualité (5 minutes)
- Invalidation du cache à la création d’un post

### 📡 API REST
- `GET /api/posts`
- `POST /api/posts`
- `GET /api/posts/{id}`

### Documentation disponible via Swagger :
- http://localhost:8000/api/docs

### 🧠 Traitement asynchrone
- Envoi d’emails lors de la réception d’un message
- Traitement via Symfony Messenger (queue async)

---

## ⚙️ Installation du projet

### 1. Cloner le projet

```bash
git clone https://github.com/salimtayeb/symfonyConnect.git
cd Symfonyconnect
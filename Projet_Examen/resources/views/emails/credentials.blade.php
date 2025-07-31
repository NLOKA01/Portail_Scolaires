@component('mail::message')
# Bienvenue sur le Portail Scolaire

Bonjour {{ $user->prenom }} {{ $user->nom }},

Votre compte a été créé avec succès sur la plateforme de gestion scolaire.

**Rôle :** {{ ucfirst($user->role) }}

**Identifiants de connexion :**
- **Email :** {{ $user->email }}
- **Mot de passe temporaire :** {{ $password }}

@component('mail::panel')
Merci de vous connecter dès que possible et de modifier votre mot de passe depuis votre espace personnel pour garantir la sécurité de votre compte.
@endcomponent

Si vous n'êtes pas à l'origine de cette inscription, merci de contacter l'administration de l'école.

Cordialement,
L'équipe Administration
@endcomponent 
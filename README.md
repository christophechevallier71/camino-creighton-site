# Camino Creighton — site web

Site vitrine statique (HTML/CSS/JS, sans framework) pour Camino Creighton.

## Structure

```
index.html            Accueil
methode.html           La méthode
accompagnement.html    Accompagnement & tarifs
apropos.html           À propos de Pilar
contact.html           Contact (formulaire)
contact.php            Traitement du formulaire (envoie un email) — nécessite de l'hébergement PHP (Hostinger en a)
merci.html              Page affichée après envoi du formulaire
css/style.css           Tous les styles
js/main.js               Menu mobile, accordéon FAQ, animations au scroll
images/                  Toutes les photos et logos
```

## Mise en ligne : GitHub → Hostinger

### 1. Mettre ce dossier sur GitHub

Le plus simple sans ligne de commande : installer **[GitHub Desktop](https://desktop.github.com/)**.

1. Ouvre GitHub Desktop, connecte-toi avec ton compte GitHub.
2. `File > Add local repository`, choisis ce dossier (`camino-creighton-site`).
3. S'il propose de créer un repo Git, accepte.
4. Clique `Publish repository` — choisis un nom (ex. `camino-creighton-site`), décoche "Keep this code private" si tu veux qu'il soit public (ça n'a pas d'importance pour le fonctionnement du site).

### 2. Créer l'hébergement Hostinger

1. Va sur [hostinger.com](https://www.hostinger.com), crée un compte et choisis un plan d'hébergement web (le moins cher convient, il inclut PHP).
2. Pendant l'inscription ou après, achète le nom de domaine **caminocreighton.com** directement via Hostinger (le plus simple — pas de configuration DNS à faire toi-même).
3. Dans le tableau de bord Hostinger (hPanel), va dans **Fichiers > Comptes FTP** (ou "Accès FTP") et note :
   - le serveur FTP (ex. `ftp.caminocreighton.com` ou une adresse IP)
   - le nom d'utilisateur FTP
   - le mot de passe FTP

### 3. Connecter GitHub à Hostinger (déploiement automatique)

Ce repo contient déjà un fichier (`.github/workflows/deploy.yml`) qui envoie automatiquement le site sur Hostinger à chaque mise à jour. Il faut juste lui donner les identifiants FTP, **en secret** (jamais en clair dans le code) :

1. Sur GitHub.com, ouvre ton repo `camino-creighton-site`.
2. `Settings > Secrets and variables > Actions > New repository secret`.
3. Crée 3 secrets avec les valeurs notées à l'étape 2 :
   - `FTP_SERVER`
   - `FTP_USERNAME`
   - `FTP_PASSWORD`
4. C'est tout. À chaque fois que le site est mis à jour et publié sur GitHub (branche `main`), il se déploie automatiquement sur Hostinger en 1-2 minutes.

Tu peux suivre le déploiement dans l'onglet **Actions** du repo GitHub.

### 4. Vérifier

Une fois le DNS propagé (parfois quelques heures après l'achat du domaine), le site est visible sur **https://caminocreighton.com**.

## Le formulaire de contact

Il utilise `contact.php`, qui envoie un email à `pilar.fertilitycare@gmail.com` grâce à la fonction `mail()` de PHP — Hostinger la supporte nativement, aucune configuration supplémentaire n'est nécessaire. Si les emails arrivent en spam, active SPF/DKIM pour le domaine dans hPanel (**Emails > Configuration DNS**).

## Faire des modifications plus tard

Le plus simple : redemande à Claude Code d'éditer les fichiers de ce dossier, puis republie sur GitHub Desktop (`Commit` puis `Push origin`) — le site se met à jour automatiquement sur Hostinger.

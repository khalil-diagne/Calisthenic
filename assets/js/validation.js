// ==============================================
// Validation du formulaire d'inscription
// ==============================================

/**
 * Valider le nom complet
 */
function validerNom(nom) {
    const nom_trim = nom.trim();
    
    // Au moins 3 caractères
    if (nom_trim.length < 3) {
        return {valid: false, message: "Le nom doit contenir au moins 3 caractères"};
    }
    
    // Maximum 100 caractères
    if (nom_trim.length > 100) {
        return {valid: false, message: "Le nom ne doit pas dépasser 100 caractères"};
    }
    
    // Seulement lettres, espaces, tirets et accents
    const regex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
    if (!regex.test(nom_trim)) {
        return {valid: false, message: "Le nom ne peut contenir que des lettres et des espaces"};
    }
    
    // Au moins 2 mots
    const words = nom_trim.split(/\s+/).filter(w => w.length > 0);
    if (words.length < 2) {
        return {valid: false, message: "Veuillez entrer votre nom complet (prénom et nom)"};
    }
    
    return {valid: true, message: ""};
}

/**
 * Valider l'email
 */
function validerEmail(email) {
    const email_trim = email.trim().toLowerCase();
    
    // Vérifier si vide
    if (email_trim.length === 0) {
        return {valid: false, message: "L'email est requis"};
    }
    
    // Format email valide
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(email_trim)) {
        return {valid: false, message: "Format d'email invalide (ex: user@example.com)"};
    }
    
    // Domaine valide (au moins 2 caractères après le @)
    const parts = email_trim.split('@');
    if (parts[1].length < 3) {
        return {valid: false, message: "Domaine email invalide"};
    }
    
    // Maximum 100 caractères
    if (email_trim.length > 100) {
        return {valid: false, message: "L'email ne doit pas dépasser 100 caractères"};
    }
    
    // Pas plus de 1 arobase
    if ((email_trim.match(/@/g) || []).length !== 1) {
        return {valid: false, message: "L'email contient trop d'arobases"};
    }
    
    return {valid: true, message: ""};
}

/**
 * Valider le téléphone
 */
function validerTelephone(telephone) {
    if (telephone.trim().length === 0) {
        return {valid: true, message: ""}; // Optionnel
    }
    
    const tel_trim = telephone.trim();
    
    // Seulement chiffres, espaces, tirets, + et parenthèses
    const regex = /^[\d\s\-\+\(\)]+$/;
    if (!regex.test(tel_trim)) {
        return {valid: false, message: "Numéro de téléphone invalide"};
    }
    
    // Au moins 7 chiffres
    const chiffres = tel_trim.replace(/\D/g, '');
    if (chiffres.length < 7) {
        return {valid: false, message: "Le téléphone doit contenir au moins 7 chiffres"};
    }
    
    // Maximum 20 chiffres
    if (chiffres.length > 20) {
        return {valid: false, message: "Le téléphone ne doit pas dépasser 20 chiffres"};
    }
    
    return {valid: true, message: ""};
}

/**
 * Valider la région
 */
function validerRegion(region) {
    if (region.trim().length === 0) {
        return {valid: false, message: "Veuillez sélectionner une région"};
    }
    
    const regions_valides = [
        "Dakar", "Thiès", "Saint-Louis", "Kaolack", "Ziguinchor",
        "Tambacounda", "Kolda", "Matam", "Louga", "Fatick", "Autre"
    ];
    
    if (!regions_valides.includes(region)) {
        return {valid: false, message: "Région non valide"};
    }
    
    return {valid: true, message: ""};
}

/**
 * Valider le niveau
 */
function validerNiveau(niveau) {
    if (niveau.trim().length === 0) {
        return {valid: false, message: "Veuillez sélectionner un niveau"};
    }
    
    const niveaux_valides = [
        "Débutant", "Intermédiaire", "Avancé", "Expert", "Élite"
    ];
    
    if (!niveaux_valides.includes(niveau)) {
        return {valid: false, message: "Niveau non valide"};
    }
    
    return {valid: true, message: ""};
}

/**
 * Valider le mot de passe
 */
function validerMotDePasse(password) {
    // Minimum 6 caractères
    if (password.length < 6) {
        return {valid: false, message: "Le mot de passe doit contenir au moins 6 caractères"};
    }
    
    // Maximum 50 caractères
    if (password.length > 50) {
        return {valid: false, message: "Le mot de passe ne doit pas dépasser 50 caractères"};
    }
    
    // Recommandation: au moins une majuscule
    if (!/[A-Z]/.test(password)) {
        return {valid: true, message: "💡 Conseil: Ajoutez une lettre majuscule pour plus de sécurité"};
    }
    
    // Recommandation: au moins un chiffre
    if (!/\d/.test(password)) {
        return {valid: true, message: "💡 Conseil: Ajoutez un chiffre pour plus de sécurité"};
    }
    
    return {valid: true, message: ""};
}

/**
 * Valider tout le formulaire
 */
function validerFormulaire(event) {
    event.preventDefault();
    
    // Récupérer les valeurs
    const nom = document.getElementById('nom').value;
    const email = document.getElementById('email').value;
    const telephone = document.getElementById('telephone').value;
    const region = document.getElementById('region').value;
    const niveau = document.getElementById('niveau').value;
    const password = document.getElementById('password').value;
    
    // Valider chaque champ
    const validations = [
        {name: 'nom', validator: validerNom, value: nom},
        {name: 'email', validator: validerEmail, value: email},
        {name: 'telephone', validator: validerTelephone, value: telephone},
        {name: 'region', validator: validerRegion, value: region},
        {name: 'niveau', validator: validerNiveau, value: niveau},
        {name: 'password', validator: validerMotDePasse, value: password}
    ];
    
    // Vérifier chaque validation
    for (let validation of validations) {
        const result = validation.validator(validation.value);
        if (!result.valid) {
            alert("❌ Erreur:\n\n" + result.message);
            return false;
        }
    }
    
    // Avertissement si recommandation
    for (let validation of validations) {
        const result = validation.validator(validation.value);
        if (result.message && result.message.includes("💡")) {
            // Conseil non-bloquant
        }
    }
    
    // Si tout est valide, soumettre le formulaire
    document.querySelector('form').submit();
    return false;
}

/**
 * Validation en temps réel (optionnel)
 */
function ajouterValidationEnTempsReel() {
    const nom = document.getElementById('nom');
    const email = document.getElementById('email');
    const telephone = document.getElementById('telephone');
    const region = document.getElementById('region');
    const niveau = document.getElementById('niveau');
    const password = document.getElementById('password');
    
    if (nom) {
        nom.addEventListener('blur', function() {
            const result = validerNom(this.value);
            afficherFeedback(this, result);
        });
    }
    
    if (email) {
        email.addEventListener('blur', function() {
            const result = validerEmail(this.value);
            afficherFeedback(this, result);
        });
    }
    
    if (telephone) {
        telephone.addEventListener('blur', function() {
            const result = validerTelephone(this.value);
            afficherFeedback(this, result);
        });
    }
    
    if (region) {
        region.addEventListener('change', function() {
            const result = validerRegion(this.value);
            afficherFeedback(this, result);
        });
    }
    
    if (niveau) {
        niveau.addEventListener('change', function() {
            const result = validerNiveau(this.value);
            afficherFeedback(this, result);
        });
    }
    
    if (password) {
        password.addEventListener('blur', function() {
            const result = validerMotDePasse(this.value);
            if (result.message) {
                console.log(result.message); // Afficher dans la console
            }
        });
    }
}

/**
 * Afficher feedback de validation
 */
function afficherFeedback(element, result) {
    // Supprimer le feedback précédent
    const feedback_ancien = element.parentElement.querySelector('.feedback-validation');
    if (feedback_ancien) {
        feedback_ancien.remove();
    }
    
    if (!result.valid && result.message) {
        const feedback = document.createElement('div');
        feedback.className = 'feedback-validation';
        feedback.style.color = '#ff6b6b';
        feedback.style.fontSize = '0.8rem';
        feedback.style.marginTop = '0.3rem';
        feedback.textContent = '⚠️ ' + result.message;
        element.parentElement.appendChild(feedback);
        
        element.style.borderColor = '#dc3545';
        element.style.background = 'rgba(220, 53, 69, 0.05)';
    } else if (result.valid && this.value !== '') {
        element.style.borderColor = 'rgba(40, 167, 69, 0.3)';
        element.style.background = 'rgba(40, 167, 69, 0.05)';
    }
}

// Initialiser la validation quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    ajouterValidationEnTempsReel();
    
    // Attacher la validation au formulaire
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', validerFormulaire);
    }
});

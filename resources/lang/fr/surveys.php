<?php

return [
    'D1' => [
        'title' => 'Démarrage',
        'questions' => [
            'q1' => [
                'label' => 'Où en êtes-vous dans la prise en main de DalyTrac ?',
                'choices' => [
                    'c1' => 'Je n’ai pas encore commencé',
                    'c2' => 'Je n’arrive pas à me connecter',
                    'c3' => 'Je ne trouve pas comment configurer mon entreprise',
                    'c4' => 'Je rencontre un problème technique',
                    'c5' => 'J’ai commencé sans difficulté',
                    'c6' => 'Je ne souhaite plus poursuivre',
                ],
            ],
            'q2' => [
                'label' => 'Quelle est la principale difficulté rencontrée ?',
                'choices' => [
                    'c1' => 'Les explications ne sont pas assez claires',
                    'c2' => 'Un message d’erreur s’affiche',
                    'c3' => 'Je ne sais pas par où commencer',
                    'c4' => 'L’import de mes données (employés, clients)',
                    'c5' => 'Je manque de temps',
                    'c6' => 'Autre (texte libre)',
                ],
            ],
            'q2_autre' => [
                'label' => 'Autre (texte libre)',
                'choices' => [],
            ],
            'q2b' => [
                'label' => 'Copiez le message d’erreur affiché.',
                'choices' => [],
            ],
            'q3' => [
                'label' => 'Quelle aide vous serait la plus utile ?',
                'choices' => [
                    'c1' => 'Un guide de démarrage',
                    'c2' => 'Une courte vidéo pas à pas',
                    'c3' => 'Une assistance technique',
                    'c4' => 'Une démonstration',
                    'c5' => 'Aucune aide pour le moment',
                ],
            ],
            'q4' => [
                'label' => 'Pourquoi ne souhaitez-vous plus poursuivre ?',
                'choices' => [
                    'c1' => 'Je n’en ai plus besoin',
                    'c2' => 'C’est trop compliqué',
                    'c3' => 'Le prix ne me convient pas',
                    'c4' => 'J’ai choisi une autre solution',
                    'c5' => 'Je manque de temps',
                    'c6' => 'Autre',
                ],
            ],
        ],
    ],
    'D2' => [
        'title' => 'Premier usage',
        'questions' => [
            'q1' => [
                'label' => 'Avez-vous commencé à utiliser DalyTrac ?',
                'choices' => [
                    'c1' => 'Oui, pour mon activité réelle',
                    'c2' => 'Oui, seulement pour découvrir',
                    'c3' => 'Non, je n’ai pas encore eu le temps',
                    'c4' => 'Non, je dois d’abord préparer mes données',
                    'c5' => 'Non, je rencontre un blocage technique',
                ],
            ],
            'q2' => [
                'label' => 'Quelle fonctionnalité avez-vous testée en priorité ?',
                'choices' => [
                    'c1' => 'Le pointage des heures',
                    'c2' => 'La gestion des employés',
                    'c3' => 'La gestion des clients',
                    'c4' => 'Les devis',
                    'c5' => 'La facturation',
                    'c6' => 'Je ne savais pas par où commencer',
                    'c7' => 'Autre (texte libre)',
                ],
            ],
            'q2_autre' => [
                'label' => 'Autre (texte libre)',
                'choices' => [],
            ],
            'q3' => [
                'label' => 'Avez-vous obtenu un premier résultat utile (heures suivies, devis ou facture créés) ?',
                'choices' => [
                    'c1' => 'Oui, clairement',
                    'c2' => 'En partie',
                    'c3' => 'Non',
                    'c4' => 'Je n’ai pas encore assez testé',
                ],
            ],
            'q4' => [
                'label' => 'Souhaitez-vous être accompagné pour la suite de votre essai ?',
                'choices' => [
                    'c1' => 'Oui, une courte démonstration',
                    'c2' => 'Oui, un guide ciblé',
                    'c3' => 'Non, je continue seul',
                    'c4' => 'Plus tard',
                ],
            ],
        ],
    ],
    'D3' => [
        'title' => 'Valeur et intention d’achat',
        'questions' => [
            'q1' => [
                'label' => 'Sur une échelle de 1 à 5, combien de temps DalyTrac vous fait-il gagner dans la gestion de votre entreprise ?',
                'choices' => [
                    'c1' => '1 – Aucun gain',
                    'c2' => '2 – Faible',
                    'c3' => '3 – Modéré',
                    'c4' => '4 – Important',
                    'c5' => '5 – Très important',
                ],
            ],
            'q2' => [
                'label' => 'Quelle fonctionnalité vous est la plus utile ?',
                'choices' => [
                    'c1' => 'Pointage des heures',
                    'c2' => 'Gestion des employés',
                    'c3' => 'Gestion des clients',
                    'c4' => 'Devis',
                    'c5' => 'Facturation',
                    'c6' => 'Aucune pour le moment',
                    'c7' => 'Autre (texte libre)',
                ],
            ],
            'q2_autre' => [
                'label' => 'Autre (texte libre)',
                'choices' => [],
            ],
            'q3' => [
                'label' => 'Qu’est-ce qui pourrait vous empêcher de continuer après l’essai ?',
                'choices' => [
                    'c1' => 'La prise en main',
                    'c2' => 'Un problème technique',
                    'c3' => 'Une fonctionnalité manque',
                    'c4' => 'Je dois obtenir l’accord d’un responsable',
                    'c5' => 'Le prix des forfaits',
                    'c6' => 'Mon équipe ne l’utilise pas encore',
                    'c7' => 'Rien, je poursuis l’évaluation',
                ],
            ],
            'q4' => [
                'label' => 'Quelle est la probabilité que vous choisissiez un forfait à la fin de l’essai ?',
                'choices' => [
                    'c1' => 'Très probable',
                    'c2' => 'Plutôt probable',
                    'c3' => 'Incertain',
                    'c4' => 'Peu probable',
                    'c5' => 'Pas du tout probable',
                ],
            ],
            'q5' => [
                'label' => 'Quel forfait envisagez-vous ?',
                'choices' => [
                    'c1' => 'Solo',
                    'c2' => 'Pro',
                    'c3' => 'Max',
                    'c4' => 'Je ne sais pas encore',
                ],
            ],
        ],
    ],
    'D4' => [
        'title' => 'Fin d’essai sans achat',
        'questions' => [
            'q1' => [
                'label' => 'Quelle est la principale raison pour laquelle vous n’avez pas choisi de forfait ?',
                'choices' => [
                    'c1' => 'Je n’ai pas eu le temps de tester suffisamment',
                    'c2' => 'Ce n’était pas le bon moment pour mon entreprise',
                    'c3' => 'La prise en main était trop difficile',
                    'c4' => 'Il manque une fonctionnalité essentielle',
                    'c5' => 'Je n’ai pas perçu assez de gain de temps',
                    'c6' => 'Le prix des forfaits ne me convient pas',
                    'c7' => 'Je dois obtenir l’accord d’un décideur',
                    'c8' => 'J’ai choisi une autre solution',
                    'c9' => 'Autre (texte libre)',
                ],
            ],
            'q1_autre' => [
                'label' => 'Autre (texte libre)',
                'choices' => [],
            ],
            'q2' => [
                'label' => 'Jusqu’où avez-vous utilisé DalyTrac pendant l’essai ?',
                'choices' => [
                    'c1' => 'J’ai créé mon compte sans vraiment l’utiliser',
                    'c2' => 'J’ai testé quelques fonctions',
                    'c3' => 'Je l’ai utilisé pour mon activité réelle',
                ],
            ],
            'q3' => [
                'label' => 'Qu’est-ce qui vous aurait donné envie de continuer ?',
                'choices' => [
                    'c1' => 'Un accompagnement au démarrage',
                    'c2' => 'Un guide ou des vidéos plus clairs',
                    'c3' => 'Une fonctionnalité supplémentaire',
                    'c4' => 'Une preuve plus claire du gain de temps',
                    'c5' => 'Un forfait ou un tarif différent',
                    'c6' => 'Davantage de temps pour tester',
                    'c7' => 'Rien, le besoin n’est pas prioritaire',
                ],
            ],
            'q4' => [
                'label' => 'Accepteriez-vous un échange de 20 minutes avec notre équipe ?',
                'choices' => [
                    'c1' => 'Oui',
                    'c2' => 'Peut-être',
                    'c3' => 'Non',
                ],
            ],
        ],
    ],
    'D5' => [
        'title' => 'Après le premier achat',
        'questions' => [
            'q1' => [
                'label' => 'Qu’est-ce qui vous a décidé à choisir DalyTrac ?',
                'choices' => [
                    'c1' => 'Le gain de temps',
                    'c2' => 'La simplicité du pointage',
                    'c3' => 'Le suivi des employés',
                    'c4' => 'La gestion des devis et factures',
                    'c5' => 'Le forfait adapté à mon entreprise',
                    'c6' => 'Une recommandation ou une démonstration',
                    'c7' => 'Autre (texte libre)',
                ],
            ],
            'q1_autre' => [
                'label' => 'Autre (texte libre)',
                'choices' => [],
            ],
            'q2' => [
                'label' => 'Utilisez-vous DalyTrac au quotidien depuis votre achat ?',
                'choices' => [
                    'c1' => 'Oui, sans difficulté',
                    'c2' => 'Oui, avec quelques difficultés',
                    'c3' => 'Non, à cause d’un problème technique',
                    'c4' => 'Non, mon équipe n’a pas encore commencé',
                ],
            ],
            'q3' => [
                'label' => 'Le forfait choisi correspond-il à vos besoins ?',
                'choices' => [
                    'c1' => 'Oui, parfaitement',
                    'c2' => 'Il me manque des possibilités',
                    'c3' => 'Il est plus complet que nécessaire',
                    'c4' => 'Je ne sais pas encore',
                ],
            ],
            'q4' => [
                'label' => 'De quelle aide auriez-vous besoin ?',
                'choices' => [
                    'c1' => 'Aucune',
                    'c2' => 'Un conseil d’utilisation',
                    'c3' => 'Une assistance technique',
                    'c4' => 'Une démonstration complémentaire',
                    'c5' => 'Une aide pour faire adopter l’outil à mon équipe',
                ],
            ],
        ],
    ],
    'D6' => [
        'title' => 'Non-renouvellement',
        'questions' => [
            'q1' => [
                'label' => 'Pour quelle raison n’avez-vous pas poursuivi votre abonnement ?',
                'choices' => [
                    'c1' => 'Mon activité a baissé ou est en pause',
                    'c2' => 'Je prévois de reprendre prochainement',
                    'c3' => 'Je rencontre un problème technique',
                    'c4' => 'DalyTrac ne répond pas suffisamment à mes besoins',
                    'c5' => 'Une fonctionnalité essentielle me manque',
                    'c6' => 'Le prix de mon forfait ne me convient plus',
                    'c7' => 'J’utilise désormais une autre solution',
                    'c8' => 'Autre (texte libre)',
                ],
            ],
            'q1_autre' => [
                'label' => 'Autre (texte libre)',
                'choices' => [],
            ],
            'q2' => [
                'label' => 'Envisagez-vous de reprendre DalyTrac à l’avenir ?',
                'choices' => [
                    'c1' => 'Oui, certainement',
                    'c2' => 'Probablement',
                    'c3' => 'Je ne sais pas',
                    'c4' => 'Probablement pas',
                    'c5' => 'Non',
                ],
            ],
            'q3' => [
                'label' => 'Un autre forfait vous conviendrait-il mieux ?',
                'choices' => [
                    'c1' => 'Oui, un forfait plus léger',
                    'c2' => 'Oui, un forfait plus complet',
                    'c3' => 'Non',
                ],
            ],
            'q4' => [
                'label' => 'Quand pouvons-nous vous recontacter ?',
                'choices' => [
                    'c1' => 'Dans un mois',
                    'c2' => 'Dans trois mois',
                    'c3' => 'Dans six mois',
                    'c4' => 'Uniquement à ma demande',
                ],
            ],
        ],
    ],
    'ui' => [
        'email_subject' => 'Vos premiers pas avec DalyTrac',
        'reminder_subject' => 'Un dernier rappel pour vos premiers pas avec DalyTrac',
        'email_intro' => 'Avez-vous pu démarrer ? Votre réponse nous aide à comprendre les difficultés rencontrées.',
        'email_reminder' => 'Si vous ne l’avez pas encore fait, partagez votre expérience de démarrage.',
        'email_safety' => 'Choisissez une réponse, puis confirmez-la sur la page. Ouvrir ce lien seul n’enregistre aucune réponse.',
        'email_expiry' => 'Ce lien est valable 15 jours après le premier envoi.',
        'eyebrow' => 'Votre expérience compte',
        'title' => 'Vos premiers pas avec DalyTrac',
        'intro' => 'Quelques questions pour comprendre votre démarrage. Seule la première est obligatoire.',
        'required_mark' => 'Obligatoire',
        'optional' => 'Facultatif',
        'choose' => 'Choisir une réponse',
        'confirm' => 'Confirmer ma première réponse',
        'submit' => 'Envoyer mes réponses',
        'preview' => 'Afficher les questions correspondantes',
        'confirmed' => 'Votre première réponse a été enregistrée.',
        'q1_locked' => 'La première réponse confirmée est conservée. Un autre choix ne peut pas la remplacer.',
        'required' => 'Veuillez répondre à la première question.',
        'invalid_answer' => 'Veuillez sélectionner une réponse autorisée.',
        'too_long' => 'Le texte libre ne peut pas dépasser 2 000 caractères.',
        'invalid_link' => 'Lien invalide ou expiré.',
        'closed' => 'Cette enquête est clôturée.',
        'already_closed' => 'Enquête déjà clôturée.',
        'thank_you' => 'Merci pour votre réponse',
        'done' => 'Vos réponses ont bien été enregistrées.',
        'privacy' => 'Ce questionnaire est accessible uniquement avec votre lien personnel.',
        'back' => 'Retour à DalyTrac',
        'no_script' => 'Sans JavaScript, utilisez le bouton pour afficher les questions correspondant à vos choix.',
        'character_limit' => '2 000 caractères maximum.',
        'saved_choice' => 'Première réponse confirmée',
    ],
];

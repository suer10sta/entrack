<?php

// Questionnaire Dalytrac, version 1. No scheduler or response workflow.
// Choices without an explicit PDF code retain their exact French value.
return [
    'reference_locale' => 'fr',
    'locales' => [
        'fr',
        'en',
        'nl',
        'es',
    ],
    'token_valid_days' => 15,
    'types' => [
        'D1' => [
            'current_version' => 1,
            'versions' => [
                '1' => [
                    'version' => 1,
                    'title_key' => 'surveys.D1.title',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'label_key' => 'surveys.D1.questions.q1.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'pas_commence',
                                    'label_key' => 'surveys.D1.questions.q1.choices.c1',
                                ],
                                [
                                    'value' => 'connexion',
                                    'label_key' => 'surveys.D1.questions.q1.choices.c2',
                                ],
                                [
                                    'value' => 'configuration',
                                    'label_key' => 'surveys.D1.questions.q1.choices.c3',
                                ],
                                [
                                    'value' => 'probleme_technique',
                                    'label_key' => 'surveys.D1.questions.q1.choices.c4',
                                ],
                                [
                                    'value' => 'reussi',
                                    'label_key' => 'surveys.D1.questions.q1.choices.c5',
                                ],
                                [
                                    'value' => 'abandon',
                                    'label_key' => 'surveys.D1.questions.q1.choices.c6',
                                ],
                            ],
                            'required' => true,
                            'condition' => null,
                            'email_clickable' => true,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2',
                            'label_key' => 'surveys.D1.questions.q2.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Les explications ne sont pas assez claires',
                                    'label_key' => 'surveys.D1.questions.q2.choices.c1',
                                ],
                                [
                                    'value' => 'Un message d’erreur s’affiche',
                                    'label_key' => 'surveys.D1.questions.q2.choices.c2',
                                ],
                                [
                                    'value' => 'Je ne sais pas par où commencer',
                                    'label_key' => 'surveys.D1.questions.q2.choices.c3',
                                ],
                                [
                                    'value' => 'L’import de mes données (employés, clients)',
                                    'label_key' => 'surveys.D1.questions.q2.choices.c4',
                                ],
                                [
                                    'value' => 'Je manque de temps',
                                    'label_key' => 'surveys.D1.questions.q2.choices.c5',
                                ],
                                [
                                    'value' => 'Autre (texte libre)',
                                    'label_key' => 'surveys.D1.questions.q2.choices.c6',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'not_in',
                                'values' => [
                                    'reussi',
                                    'abandon',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => 'q2_autre',
                        ],
                        [
                            'id' => 'q2_autre',
                            'label_key' => 'surveys.D1.questions.q2_autre.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q2',
                                'operator' => 'in',
                                'values' => [
                                    'Autre (texte libre)',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2b',
                            'label_key' => 'surveys.D1.questions.q2b.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q2',
                                'operator' => 'in',
                                'values' => [
                                    'Un message d’erreur s’affiche',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q3',
                            'label_key' => 'surveys.D1.questions.q3.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Un guide de démarrage',
                                    'label_key' => 'surveys.D1.questions.q3.choices.c1',
                                ],
                                [
                                    'value' => 'Une courte vidéo pas à pas',
                                    'label_key' => 'surveys.D1.questions.q3.choices.c2',
                                ],
                                [
                                    'value' => 'Une assistance technique',
                                    'label_key' => 'surveys.D1.questions.q3.choices.c3',
                                ],
                                [
                                    'value' => 'Une démonstration',
                                    'label_key' => 'surveys.D1.questions.q3.choices.c4',
                                ],
                                [
                                    'value' => 'Aucune aide pour le moment',
                                    'label_key' => 'surveys.D1.questions.q3.choices.c5',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'not_in',
                                'values' => [
                                    'reussi',
                                    'abandon',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q4',
                            'label_key' => 'surveys.D1.questions.q4.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Je n’en ai plus besoin',
                                    'label_key' => 'surveys.D1.questions.q4.choices.c1',
                                ],
                                [
                                    'value' => 'C’est trop compliqué',
                                    'label_key' => 'surveys.D1.questions.q4.choices.c2',
                                ],
                                [
                                    'value' => 'Le prix ne me convient pas',
                                    'label_key' => 'surveys.D1.questions.q4.choices.c3',
                                ],
                                [
                                    'value' => 'J’ai choisi une autre solution',
                                    'label_key' => 'surveys.D1.questions.q4.choices.c4',
                                ],
                                [
                                    'value' => 'Je manque de temps',
                                    'label_key' => 'surveys.D1.questions.q4.choices.c5',
                                ],
                                [
                                    'value' => 'Autre',
                                    'label_key' => 'surveys.D1.questions.q4.choices.c6',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'abandon',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                    ],
                ],
            ],
        ],
        'D2' => [
            'current_version' => 1,
            'versions' => [
                '1' => [
                    'version' => 1,
                    'title_key' => 'surveys.D2.title',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'label_key' => 'surveys.D2.questions.q1.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'usage_reel',
                                    'label_key' => 'surveys.D2.questions.q1.choices.c1',
                                ],
                                [
                                    'value' => 'decouverte',
                                    'label_key' => 'surveys.D2.questions.q1.choices.c2',
                                ],
                                [
                                    'value' => 'pas_temps',
                                    'label_key' => 'surveys.D2.questions.q1.choices.c3',
                                ],
                                [
                                    'value' => 'pas_donnees',
                                    'label_key' => 'surveys.D2.questions.q1.choices.c4',
                                ],
                                [
                                    'value' => 'blocage',
                                    'label_key' => 'surveys.D2.questions.q1.choices.c5',
                                ],
                            ],
                            'required' => true,
                            'condition' => null,
                            'email_clickable' => true,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2',
                            'label_key' => 'surveys.D2.questions.q2.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Le pointage des heures',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c1',
                                ],
                                [
                                    'value' => 'La gestion des employés',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c2',
                                ],
                                [
                                    'value' => 'La gestion des clients',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c3',
                                ],
                                [
                                    'value' => 'Les devis',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c4',
                                ],
                                [
                                    'value' => 'La facturation',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c5',
                                ],
                                [
                                    'value' => 'Je ne savais pas par où commencer',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c6',
                                ],
                                [
                                    'value' => 'Autre (texte libre)',
                                    'label_key' => 'surveys.D2.questions.q2.choices.c7',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'usage_reel',
                                    'decouverte',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => 'q2_autre',
                        ],
                        [
                            'id' => 'q2_autre',
                            'label_key' => 'surveys.D2.questions.q2_autre.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q2',
                                'operator' => 'in',
                                'values' => [
                                    'Autre (texte libre)',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q3',
                            'label_key' => 'surveys.D2.questions.q3.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui, clairement',
                                    'label_key' => 'surveys.D2.questions.q3.choices.c1',
                                ],
                                [
                                    'value' => 'En partie',
                                    'label_key' => 'surveys.D2.questions.q3.choices.c2',
                                ],
                                [
                                    'value' => 'Non',
                                    'label_key' => 'surveys.D2.questions.q3.choices.c3',
                                ],
                                [
                                    'value' => 'Je n’ai pas encore assez testé',
                                    'label_key' => 'surveys.D2.questions.q3.choices.c4',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'usage_reel',
                                    'decouverte',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q4',
                            'label_key' => 'surveys.D2.questions.q4.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui, une courte démonstration',
                                    'label_key' => 'surveys.D2.questions.q4.choices.c1',
                                ],
                                [
                                    'value' => 'Oui, un guide ciblé',
                                    'label_key' => 'surveys.D2.questions.q4.choices.c2',
                                ],
                                [
                                    'value' => 'Non, je continue seul',
                                    'label_key' => 'surveys.D2.questions.q4.choices.c3',
                                ],
                                [
                                    'value' => 'Plus tard',
                                    'label_key' => 'surveys.D2.questions.q4.choices.c4',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                    ],
                ],
            ],
        ],
        'D3' => [
            'current_version' => 1,
            'versions' => [
                '1' => [
                    'version' => 1,
                    'title_key' => 'surveys.D3.title',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'label_key' => 'surveys.D3.questions.q1.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => '1',
                                    'label_key' => 'surveys.D3.questions.q1.choices.c1',
                                ],
                                [
                                    'value' => '2',
                                    'label_key' => 'surveys.D3.questions.q1.choices.c2',
                                ],
                                [
                                    'value' => '3',
                                    'label_key' => 'surveys.D3.questions.q1.choices.c3',
                                ],
                                [
                                    'value' => '4',
                                    'label_key' => 'surveys.D3.questions.q1.choices.c4',
                                ],
                                [
                                    'value' => '5',
                                    'label_key' => 'surveys.D3.questions.q1.choices.c5',
                                ],
                            ],
                            'required' => true,
                            'condition' => null,
                            'email_clickable' => true,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2',
                            'label_key' => 'surveys.D3.questions.q2.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Pointage des heures',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c1',
                                ],
                                [
                                    'value' => 'Gestion des employés',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c2',
                                ],
                                [
                                    'value' => 'Gestion des clients',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c3',
                                ],
                                [
                                    'value' => 'Devis',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c4',
                                ],
                                [
                                    'value' => 'Facturation',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c5',
                                ],
                                [
                                    'value' => 'Aucune pour le moment',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c6',
                                ],
                                [
                                    'value' => 'Autre (texte libre)',
                                    'label_key' => 'surveys.D3.questions.q2.choices.c7',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => 'q2_autre',
                        ],
                        [
                            'id' => 'q2_autre',
                            'label_key' => 'surveys.D3.questions.q2_autre.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q2',
                                'operator' => 'in',
                                'values' => [
                                    'Autre (texte libre)',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q3',
                            'label_key' => 'surveys.D3.questions.q3.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'La prise en main',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c1',
                                ],
                                [
                                    'value' => 'Un problème technique',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c2',
                                ],
                                [
                                    'value' => 'Une fonctionnalité manque',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c3',
                                ],
                                [
                                    'value' => 'Je dois obtenir l’accord d’un responsable',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c4',
                                ],
                                [
                                    'value' => 'Le prix des forfaits',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c5',
                                ],
                                [
                                    'value' => 'Mon équipe ne l’utilise pas encore',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c6',
                                ],
                                [
                                    'value' => 'Rien, je poursuis l’évaluation',
                                    'label_key' => 'surveys.D3.questions.q3.choices.c7',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q4',
                            'label_key' => 'surveys.D3.questions.q4.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Très probable',
                                    'label_key' => 'surveys.D3.questions.q4.choices.c1',
                                ],
                                [
                                    'value' => 'Plutôt probable',
                                    'label_key' => 'surveys.D3.questions.q4.choices.c2',
                                ],
                                [
                                    'value' => 'Incertain',
                                    'label_key' => 'surveys.D3.questions.q4.choices.c3',
                                ],
                                [
                                    'value' => 'Peu probable',
                                    'label_key' => 'surveys.D3.questions.q4.choices.c4',
                                ],
                                [
                                    'value' => 'Pas du tout probable',
                                    'label_key' => 'surveys.D3.questions.q4.choices.c5',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q5',
                            'label_key' => 'surveys.D3.questions.q5.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Solo',
                                    'label_key' => 'surveys.D3.questions.q5.choices.c1',
                                ],
                                [
                                    'value' => 'Pro',
                                    'label_key' => 'surveys.D3.questions.q5.choices.c2',
                                ],
                                [
                                    'value' => 'Max',
                                    'label_key' => 'surveys.D3.questions.q5.choices.c3',
                                ],
                                [
                                    'value' => 'Je ne sais pas encore',
                                    'label_key' => 'surveys.D3.questions.q5.choices.c4',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q4',
                                'operator' => 'in',
                                'values' => [
                                    'Très probable',
                                    'Plutôt probable',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                    ],
                ],
            ],
        ],
        'D4' => [
            'current_version' => 1,
            'versions' => [
                '1' => [
                    'version' => 1,
                    'title_key' => 'surveys.D4.title',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'label_key' => 'surveys.D4.questions.q1.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Je n’ai pas eu le temps de tester suffisamment',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c1',
                                ],
                                [
                                    'value' => 'Ce n’était pas le bon moment pour mon entreprise',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c2',
                                ],
                                [
                                    'value' => 'La prise en main était trop difficile',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c3',
                                ],
                                [
                                    'value' => 'Il manque une fonctionnalité essentielle',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c4',
                                ],
                                [
                                    'value' => 'Je n’ai pas perçu assez de gain de temps',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c5',
                                ],
                                [
                                    'value' => 'Le prix des forfaits ne me convient pas',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c6',
                                ],
                                [
                                    'value' => 'Je dois obtenir l’accord d’un décideur',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c7',
                                ],
                                [
                                    'value' => 'J’ai choisi une autre solution',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c8',
                                ],
                                [
                                    'value' => 'Autre (texte libre)',
                                    'label_key' => 'surveys.D4.questions.q1.choices.c9',
                                ],
                            ],
                            'required' => true,
                            'condition' => null,
                            'email_clickable' => true,
                            'max_length' => null,
                            'free_text_question_id' => 'q1_autre',
                        ],
                        [
                            'id' => 'q1_autre',
                            'label_key' => 'surveys.D4.questions.q1_autre.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'Autre (texte libre)',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2',
                            'label_key' => 'surveys.D4.questions.q2.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'J’ai créé mon compte sans vraiment l’utiliser',
                                    'label_key' => 'surveys.D4.questions.q2.choices.c1',
                                ],
                                [
                                    'value' => 'J’ai testé quelques fonctions',
                                    'label_key' => 'surveys.D4.questions.q2.choices.c2',
                                ],
                                [
                                    'value' => 'Je l’ai utilisé pour mon activité réelle',
                                    'label_key' => 'surveys.D4.questions.q2.choices.c3',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q3',
                            'label_key' => 'surveys.D4.questions.q3.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Un accompagnement au démarrage',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c1',
                                ],
                                [
                                    'value' => 'Un guide ou des vidéos plus clairs',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c2',
                                ],
                                [
                                    'value' => 'Une fonctionnalité supplémentaire',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c3',
                                ],
                                [
                                    'value' => 'Une preuve plus claire du gain de temps',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c4',
                                ],
                                [
                                    'value' => 'Un forfait ou un tarif différent',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c5',
                                ],
                                [
                                    'value' => 'Davantage de temps pour tester',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c6',
                                ],
                                [
                                    'value' => 'Rien, le besoin n’est pas prioritaire',
                                    'label_key' => 'surveys.D4.questions.q3.choices.c7',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q4',
                            'label_key' => 'surveys.D4.questions.q4.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui',
                                    'label_key' => 'surveys.D4.questions.q4.choices.c1',
                                ],
                                [
                                    'value' => 'Peut-être',
                                    'label_key' => 'surveys.D4.questions.q4.choices.c2',
                                ],
                                [
                                    'value' => 'Non',
                                    'label_key' => 'surveys.D4.questions.q4.choices.c3',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                    ],
                ],
            ],
        ],
        'D5' => [
            'current_version' => 1,
            'versions' => [
                '1' => [
                    'version' => 1,
                    'title_key' => 'surveys.D5.title',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'label_key' => 'surveys.D5.questions.q1.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Le gain de temps',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c1',
                                ],
                                [
                                    'value' => 'La simplicité du pointage',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c2',
                                ],
                                [
                                    'value' => 'Le suivi des employés',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c3',
                                ],
                                [
                                    'value' => 'La gestion des devis et factures',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c4',
                                ],
                                [
                                    'value' => 'Le forfait adapté à mon entreprise',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c5',
                                ],
                                [
                                    'value' => 'Une recommandation ou une démonstration',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c6',
                                ],
                                [
                                    'value' => 'Autre (texte libre)',
                                    'label_key' => 'surveys.D5.questions.q1.choices.c7',
                                ],
                            ],
                            'required' => true,
                            'condition' => null,
                            'email_clickable' => true,
                            'max_length' => null,
                            'free_text_question_id' => 'q1_autre',
                        ],
                        [
                            'id' => 'q1_autre',
                            'label_key' => 'surveys.D5.questions.q1_autre.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'Autre (texte libre)',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2',
                            'label_key' => 'surveys.D5.questions.q2.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui, sans difficulté',
                                    'label_key' => 'surveys.D5.questions.q2.choices.c1',
                                ],
                                [
                                    'value' => 'Oui, avec quelques difficultés',
                                    'label_key' => 'surveys.D5.questions.q2.choices.c2',
                                ],
                                [
                                    'value' => 'Non, à cause d’un problème technique',
                                    'label_key' => 'surveys.D5.questions.q2.choices.c3',
                                ],
                                [
                                    'value' => 'Non, mon équipe n’a pas encore commencé',
                                    'label_key' => 'surveys.D5.questions.q2.choices.c4',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q3',
                            'label_key' => 'surveys.D5.questions.q3.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui, parfaitement',
                                    'label_key' => 'surveys.D5.questions.q3.choices.c1',
                                ],
                                [
                                    'value' => 'Il me manque des possibilités',
                                    'label_key' => 'surveys.D5.questions.q3.choices.c2',
                                ],
                                [
                                    'value' => 'Il est plus complet que nécessaire',
                                    'label_key' => 'surveys.D5.questions.q3.choices.c3',
                                ],
                                [
                                    'value' => 'Je ne sais pas encore',
                                    'label_key' => 'surveys.D5.questions.q3.choices.c4',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q4',
                            'label_key' => 'surveys.D5.questions.q4.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Aucune',
                                    'label_key' => 'surveys.D5.questions.q4.choices.c1',
                                ],
                                [
                                    'value' => 'Un conseil d’utilisation',
                                    'label_key' => 'surveys.D5.questions.q4.choices.c2',
                                ],
                                [
                                    'value' => 'Une assistance technique',
                                    'label_key' => 'surveys.D5.questions.q4.choices.c3',
                                ],
                                [
                                    'value' => 'Une démonstration complémentaire',
                                    'label_key' => 'surveys.D5.questions.q4.choices.c4',
                                ],
                                [
                                    'value' => 'Une aide pour faire adopter l’outil à mon équipe',
                                    'label_key' => 'surveys.D5.questions.q4.choices.c5',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                    ],
                ],
            ],
        ],
        'D6' => [
            'current_version' => 1,
            'versions' => [
                '1' => [
                    'version' => 1,
                    'title_key' => 'surveys.D6.title',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'label_key' => 'surveys.D6.questions.q1.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Mon activité a baissé ou est en pause',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c1',
                                ],
                                [
                                    'value' => 'Je prévois de reprendre prochainement',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c2',
                                ],
                                [
                                    'value' => 'Je rencontre un problème technique',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c3',
                                ],
                                [
                                    'value' => 'Dalytrac ne répond pas suffisamment à mes besoins',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c4',
                                ],
                                [
                                    'value' => 'Une fonctionnalité essentielle me manque',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c5',
                                ],
                                [
                                    'value' => 'Le prix de mon forfait ne me convient plus',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c6',
                                ],
                                [
                                    'value' => 'J’utilise désormais une autre solution',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c7',
                                ],
                                [
                                    'value' => 'Autre (texte libre)',
                                    'label_key' => 'surveys.D6.questions.q1.choices.c8',
                                ],
                            ],
                            'required' => true,
                            'condition' => null,
                            'email_clickable' => true,
                            'max_length' => null,
                            'free_text_question_id' => 'q1_autre',
                        ],
                        [
                            'id' => 'q1_autre',
                            'label_key' => 'surveys.D6.questions.q1_autre.label',
                            'type' => 'text',
                            'choices' => [],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'Autre (texte libre)',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => 2000,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q2',
                            'label_key' => 'surveys.D6.questions.q2.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui, certainement',
                                    'label_key' => 'surveys.D6.questions.q2.choices.c1',
                                ],
                                [
                                    'value' => 'Probablement',
                                    'label_key' => 'surveys.D6.questions.q2.choices.c2',
                                ],
                                [
                                    'value' => 'Je ne sais pas',
                                    'label_key' => 'surveys.D6.questions.q2.choices.c3',
                                ],
                                [
                                    'value' => 'Probablement pas',
                                    'label_key' => 'surveys.D6.questions.q2.choices.c4',
                                ],
                                [
                                    'value' => 'Non',
                                    'label_key' => 'surveys.D6.questions.q2.choices.c5',
                                ],
                            ],
                            'required' => false,
                            'condition' => null,
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q3',
                            'label_key' => 'surveys.D6.questions.q3.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Oui, un forfait plus léger',
                                    'label_key' => 'surveys.D6.questions.q3.choices.c1',
                                ],
                                [
                                    'value' => 'Oui, un forfait plus complet',
                                    'label_key' => 'surveys.D6.questions.q3.choices.c2',
                                ],
                                [
                                    'value' => 'Non',
                                    'label_key' => 'surveys.D6.questions.q3.choices.c3',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q1',
                                'operator' => 'in',
                                'values' => [
                                    'Dalytrac ne répond pas suffisamment à mes besoins',
                                    'Le prix de mon forfait ne me convient plus',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                        [
                            'id' => 'q4',
                            'label_key' => 'surveys.D6.questions.q4.label',
                            'type' => 'single_choice',
                            'choices' => [
                                [
                                    'value' => 'Dans un mois',
                                    'label_key' => 'surveys.D6.questions.q4.choices.c1',
                                ],
                                [
                                    'value' => 'Dans trois mois',
                                    'label_key' => 'surveys.D6.questions.q4.choices.c2',
                                ],
                                [
                                    'value' => 'Dans six mois',
                                    'label_key' => 'surveys.D6.questions.q4.choices.c3',
                                ],
                                [
                                    'value' => 'Uniquement à ma demande',
                                    'label_key' => 'surveys.D6.questions.q4.choices.c4',
                                ],
                            ],
                            'required' => false,
                            'condition' => [
                                'question_id' => 'q2',
                                'operator' => 'not_in',
                                'values' => [
                                    'Non',
                                ],
                            ],
                            'email_clickable' => false,
                            'max_length' => null,
                            'free_text_question_id' => null,
                        ],
                    ],
                ],
            ],
        ],
    ],
];

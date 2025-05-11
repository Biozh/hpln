import { Col, Container, Row, Button } from "react-bootstrap";
import { useTheme } from "../utils/useTheme";

import logo from '../assets/img/logo.svg'
import logo_dark from '../assets/img/logo_dark.svg'


export default function Legals() {
    const { theme } = useTheme()

    return (<>
        <div className="modal fade" id="legals" tabIndex="-1" aria-labelledby="legalsLabel" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered modal-lg">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="legalsLabel">Mentions légales</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div className="modal-body text-start text-body-reverse">


                        <div className="container py-2">
                            <h1 className="mb-4">Mentions légales</h1>

                            <section className="mb-3">
                                <h2 className="h4">Préambule</h2>
                                <p>
                                    Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique,
                                    et du Règlement Général sur la Protection des Données (RGPD), il est précisé aux utilisateurs du site hpln.fr
                                    l'identité des différents intervenants dans le cadre de sa réalisation, de son suivi et du traitement des données personnelles.
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Édition du site</h2>
                                <p>
                                    Le présent site, accessible à l’URL <a href="https://hpln.fr">https://hpln.fr</a>, est édité par :
                                </p>
                                <ul>
                                    <li>HPLN, association loi 1901, enregistrée sous le numéro W353023363 auprès de la Préfecture d’Ille-et-Vilaine</li>
                                    <li>Siège social : 54 avenue Ledru-Rollin, 75012 Paris</li>
                                    <li>Représentée par Léon Scheinkmann et Hippolyte Berthault, en qualité de co-présidents</li>
                                </ul>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Création, développement et maintenance</h2>
                                <p>
                                    Le site a été conçu, développé et est maintenu par la société <strong>Biozh</strong>, représentée par Arsène Bidan et Hector Bidan, développeurs web.
                                    Biozh agit en tant que prestataire technique et sous-traitant pour le compte de l’association HPLN.
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Webmaster</h2>
                                <p>
                                    Le webmaster du site est la société <strong>Biozh</strong>.<br />
                                    Contact : <a href="mailto:support@biozh-studio.fr">support@biozh-studio.fr</a>
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Hébergement</h2>
                                <p>
                                    Le site est hébergé par :<br />
                                    Hostinger International Ltd<br />
                                    61 Lordou Vironos Street, 6023 Larnaca, Chypre<br />
                                    Contact : <a href="https://www.hostinger.fr/contact" target="_blank">hostinger.fr/contact</a>
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Directeurs de la publication</h2>
                                <p>
                                    Les Directeurs de la publication sont Léon Scheinkmann et Hippolyte Berthault, en qualité de co-présidents de l'association HPLN.
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Nous contacter</h2>
                                <ul>
                                    <li>Par téléphone : +33 7 81 36 01 01</li>
                                    <li>Par email : <a href="mailto:hpln.leon.hippo@gmail.com">hpln.leon.hippo@gmail.com</a></li>
                                    <li>Par courrier : 54 avenue Ledru-Rollin, 75012 Paris</li>
                                </ul>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Données personnelles</h2>
                                <p>
                                    L’association HPLN est responsable du traitement des données personnelles collectées sur le site.
                                    La société Biozh intervient en tant que sous-traitant, avec un accès limité aux données uniquement dans le cadre
                                    de la maintenance technique et de l’hébergement du site.
                                </p>
                                <p>
                                    Les données éventuellement recueillies via le formulaire de contact (nom, adresse email, message) sont utilisées
                                    uniquement pour répondre aux sollicitations des utilisateurs. Elles ne sont ni cédées, ni vendues à des tiers.
                                </p>
                                <p>
                                    La base légale du traitement est le consentement de l'utilisateur. Les données sont conservées jusqu’à 3 ans maximum,
                                    sauf obligation légale ou intérêt justifié. Elles sont supprimées ou anonymisées lorsqu’elles ne sont plus nécessaires.
                                </p>
                                <p>
                                    Conformément au RGPD, les utilisateurs disposent des droits suivants :
                                </p>
                                <ul>
                                    <li>Droit d'accès, de rectification, d’effacement, de limitation et d’opposition</li>
                                    <li>Droit de retirer leur consentement à tout moment</li>
                                    <li>Droit d’introduire une réclamation auprès de la CNIL</li>
                                </ul>
                                <p>
                                    Pour exercer ces droits, contactez-nous à l’adresse indiquée ci-dessus.
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Cookies</h2>
                                <p>
                                    Le site peut utiliser des cookies strictement nécessaires à son fonctionnement (cookies de session).
                                    D’autres cookies (notamment pour la mesure d’audience) ne sont déposés qu’avec votre consentement, via une bannière
                                    de gestion des cookies. Vous pouvez modifier vos préférences à tout moment.
                                </p>
                            </section>

                            <section className="mb-3">
                                <h2 className="h4">Propriété intellectuelle</h2>
                                <p>
                                    Tous les contenus présents sur le site (textes, images, vidéos, sons, graphismes, logos…) sont la propriété exclusive
                                    de l'association HPLN, sauf mention contraire.<br />
                                    Toute reproduction, distribution, modification ou utilisation sans autorisation écrite est strictement interdite.
                                </p>
                            </section>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </>)
}




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

                        <h5>Préambule</h5>
                        <p>
                            Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance en l'économie numérique,
                            il est précisé aux utilisateurs du site <strong>hpln.fr</strong> l'identité des différents intervenants dans le cadre
                            de sa réalisation et de son suivi.
                        </p>

                        <h5>Édition du site</h5>
                        <p>
                            Le présent site, accessible à l'URL <a href="https://hpln.fr" target="_blank">https://hpln.fr</a> (le « Site »),
                            est édité par :
                        </p>
                        <p>
                            <strong>HPLN Association</strong>, enregistrée sous le numéro <strong>W353023363</strong> auprès de la Préfecture de
                            Paris,<br />
                            Siège : 54 avenue Ledru-Rollin, 75012 Paris<br />
                            Représentée par Léon Scheinkmann, dûment habilité en qualité de Président.
                        </p>

                        <h5>Création et développement</h5>
                        <p>
                            Le site a été conçu, développé et est maintenu par la société <strong>Biozh</strong>, représentée par Arsène Bidan et
                            Hector Bidan, développeurs web.
                            Biozh agit en tant que prestataire technique pour le compte de l'association HPLN.
                        </p>

                        <h5>Webmaster</h5>
                        <p>
                            Le webmaster du site est la société <strong>Biozh</strong>.<br />
                            Contact : <a href="mailto:support@biozh-studio.fr">support@biozh-studio.fr</a>
                        </p>

                        <h5>Hébergement</h5>
                        <p>
                            Le Site est hébergé par <strong>Hostinger International Ltd.</strong><br />
                            Adresse : 61 Lordou Vironos Street, 6023 Larnaca, Chypre<br />
                            Contact : <a href="https://www.hostinger.fr/contact" target="_blank">https://www.hostinger.fr/contact</a>
                        </p>

                        <h5>Directeur de publication</h5>
                        <p>
                            Le Directeur de la publication est <strong>Léon Scheinkmann</strong>, en qualité de Président de l'association HPLN.
                        </p>

                        <h5>Nous contacter</h5>
                        <p>
                            Par téléphone : <a href="tel:+33781360101">+33 7 81 36 01 01</a><br />
                            Par email : <a href="mailto:scheinkmannleon35000@gmail.com">scheinkmannleon35000@gmail.com</a><br />
                            Par courrier : 54 avenue Ledru-Rollin, 75012 Paris
                        </p>

                        <h5>Données personnelles</h5>
                        <p>
                            L'association <strong>HPLN</strong> est responsable du traitement des données.
                            La société <strong>Biozh</strong> intervient en tant que sous-traitant technique, avec un accès aux données uniquement
                            dans le cadre de la maintenance et de l'hébergement du site.
                        </p>
                        <p>
                            Les utilisateurs disposent d’un droit d’accès, de rectification, de suppression et d’opposition concernant leurs
                            données personnelles,
                            qu’ils peuvent exercer en contactant l’association aux coordonnées indiquées ci-dessus.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </>)
}




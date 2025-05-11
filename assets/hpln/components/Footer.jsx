import { Col, Container, Row, Button } from "react-bootstrap";
import { useTheme } from "../utils/useTheme";

import logo from '../assets/img/logo.svg'
import logo_dark from '../assets/img/logo_dark.svg'


export default function Footer() {
    const { theme } = useTheme()

    return (<>
        <Container as="footer" fluid className="d-flex flex-column justify-content-center bg-body-reverse">
            <Container>
                <Row className="justify-content-between align-items-center my-3 ">
                    <Col className="text-center mb-lg-0">
                        <div className="d-flex align-items-center">
                            <img
                                src={theme === "dark" ? logo : logo_dark}
                                width="48"
                                height="48"
                                className="d-flex align-top"
                                alt="HPLN Production"
                            />
                            <h2 className="mb-0 ms-2 fw-bold">
                                HPLN Production
                            </h2>
                        </div>
                    </Col>
                    <Col className="d-flex align-items-center justify-content-end mt-5 mt-md-0 d-none d-md-block" >
                        <div className="d-flex align-items-center justify-content-end gap-5 text-center">
                            <a href="#nos-projets" className="text-decoration-none text-body">Nos projets</a>
                            <a href="#l-association" className="text-decoration-none text-body">L'association</a>
                            <a href="#nous-contacter" className="text-decoration-none text-body">Nous contacter</a>
                        </div>
                    </Col>
                </Row>
            </Container>
        </Container>
        <Container as="footer" fluid className="d-flex flex-column justify-content-center">
            <Container>
                <Row className="justify-content-between my-2 ">
                    <Col sm={12} md="auto" className="text-center">
                        <div className="d-flex align-items-center">
                            <span className="mb-0">
                                Site web réalisé par <a href="https://biozh-studio.fr" className="text-decoration-none text-body"><b>BIOZH</b></a> © Tous droits réservés
                            </span>
                        </div>
                    </Col>
                    <Col sm={12} md="auto" className=" d-flex align-items-center mt-3 mt-md-0 justify-content-center justify-content-md-end" >
                        <div className="d-flex align-items-center justify-content-end gap-5">
                            <a href="" data-bs-toggle="modal" data-bs-target="#legals" className="text-body fs-6">Mentions légales</a>
                        </div>
                    </Col>
                </Row>
            </Container>
        </Container >
    </>)
}
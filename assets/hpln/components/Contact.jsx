import { Toast } from "bootstrap";
import { useRef, useState } from "react";
import { Button, Col, Container, Form, Row } from "react-bootstrap";

export default function Contact() {
    const [validated, setValidated] = useState(false);
    const [loading, setLoading] = useState(false);

    const handleSubmit = (event) => {
        const form = event.currentTarget;
        if (form.checkValidity() === true) {
            event.preventDefault();
            event.stopPropagation();
        } else {

            form.classList.add('was-validated');
            event.preventDefault();
            event.stopPropagation();
            return

        }
        if (form.checkValidity()) {
            setLoading(true)
            fetch(APP_CONTACT_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    firstname: form.firstname.value,
                    lastname: form.lastname.value,
                    email: form.email.value,
                    message: form.message.value,
                }),
            })
                .then((response) => {
                    setLoading(false)
                    if (response.ok) {
                        const toastEl = document.getElementById('successToast');
                        const toast = new Toast(toastEl);
                        toast.show();

                        form.reset();
                        form.classList.remove('was-validated');
                    } else {
                        const toastEl = document.getElementById('errorToast');
                        const toast = new Toast(toastEl);
                        toast.show();
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        }
        setValidated(true);
    };

    return (<>
        <div className="anchor" id="nous-contacter" style={{ top: 0 }}></div>
        <Container fluid className="section flex-center bg-body py-4 py-lg-0">
            <Container>
                <Row>
                    <Col lg={7} className="mb-5 mb-lg-0">
                        <h2 className="mb-3 mb-lg-5">Écrivez-nous</h2>
                        <Form noValidate className="needs-validation" onSubmit={handleSubmit}>
                            <Row>
                                <Form.Group as={Col} xs={6} controlId="firstname">
                                    <Form.Label>Prénom *</Form.Label>
                                    <Form.Control required type="text" placeholder="Votre prénom" className="px-3 rounded-pill bg-transparent" />
                                </Form.Group>
                                <Form.Group as={Col} xs={6} controlId="lastname" className="mb-3">
                                    <Form.Label>Nom *</Form.Label>
                                    <Form.Control required type="text" placeholder="Votre nom" className="px-3 rounded-pill bg-transparent" />
                                </Form.Group>
                                <Form.Group as={Col} xs={12} controlId="email" className="mb-3">
                                    <Form.Label>Email *</Form.Label>
                                    <Form.Control required type="email" placeholder="Votre email" className="px-3 rounded-pill bg-transparent" />
                                </Form.Group>
                                <Form.Group as={Col} xs={12} controlId="message" className="mb-3">
                                    <Form.Label>Message *</Form.Label>
                                    <Form.Control as="textarea" required type="text" placeholder="Bonjour," rows="8" className="px-3 rounded-4 bg-transparent" />
                                </Form.Group>
                                <Col className="text-end">
                                    <button disabled={loading} type="submit" className="btn-body rounded-pill px-4">
                                        {!loading ? "Envoyer" : <span class="ms-1 spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>}
                                    </button>
                                </Col>
                            </Row>
                        </Form>
                    </Col>
                    <Col xs={0} lg={1}></Col>
                    <Col xs={12} lg={4}>
                        <h2 className="mb-3 mb-lg-5">Nos informations</h2>
                        <div className="d-flex mb-4">
                            <Col xs={2} sm={1} lg={2} xl={2} className="flex-center justify-content-start">
                                <i className="fa-solid fa-2xl fa-envelope"></i>
                            </Col>
                            <Col xs={10} sm={11} lg={10} xl={10} className="flex-center justify-content-start">
                                <span>hpln.leon.hippo@gmail.com</span>
                            </Col>
                        </div>
                        <div className="d-flex mb-4">
                            <Col xs={2} sm={1} lg={2} xl={2} className="flex-center justify-content-start">
                                <i className="fa-solid fa-2xl fa-phone"></i>
                            </Col>
                            <Col xs={10} sm={11} lg={10} xl={10} className="flex-center justify-content-start">
                                <div className="d-flex flex-column justify-content-center">
                                    <p className="m-0">Léon: 07 81 36 01 01</p>
                                    <p className="m-0">Hipolyte: 07 66 62 89 31</p>
                                </div>
                            </Col>
                        </div>
                        <a href="https://www.youtube.com/@hpln247" target="_blank" className="d-flex mb-4 text-decoration-none">
                            <Col xs={2} sm={1} lg={2} xl={2} className="flex-center justify-content-start">
                                <i className="fa-brands text-body fa-2xl fa-youtube"></i>
                            </Col>
                            <Col xs={10} sm={11} lg={10} xl={10} className="flex-center justify-content-start">
                                <span className="text-body text-decoration-underline">@__HPLN</span>
                            </Col>
                        </a>
                        <a href="https://www.linkedin.com/company/hpln-association/" target="_blank" className="d-flex mb-4">
                            <Col xs={2} sm={1} lg={2} xl={2} className="flex-center justify-content-start">
                                <i className="fa-brands text-body fa-2xl fa-linkedin"></i>
                            </Col>
                            <Col xs={10} sm={11} lg={10} xl={10} className="flex-center justify-content-start">
                                <span className="text-body">HPLN Association</span>
                            </Col>
                        </a>
                        <a href="https://www.instagram.com/__hpln/" target="_blank" className="d-flex mb-4">
                            <Col xs={2} sm={1} lg={2} xl={2} className="flex-center justify-content-start">
                                <i className="fa-brands text-body fa-2xl fa-instagram"></i>
                            </Col>
                            <Col xs={10} sm={11} lg={10} xl={10} className="flex-center justify-content-start">
                                <span className="text-body">@hpln247</span>
                            </Col>
                        </a>
                    </Col>
                </Row>
            </Container>
        </Container>

        <div className="toast-container position-fixed bottom-0 start-0 p-3 ">
            <div id="successToast" className="toast align-items-center border border-2 border-body-reverse bg-body-reverse text-bg-success" role="alert" aria-live="assertive" aria-atomic="true">
                <div className="d-flex">
                    <div className="toast-body">
                        Message envoyé avec succès !
                    </div>
                </div>
            </div>
            <div id="errorToast" className="toast align-items-center border border-2 border-body-reverse bg-body-reverse text-bg-danger" role="alert" aria-live="assertive" aria-atomic="true">
                <div className="d-flex">
                    <div className="toast-body">
                        Erreur lors de l'envoi du message !
                    </div>
                </div>
            </div>
        </div>

    </>)
}
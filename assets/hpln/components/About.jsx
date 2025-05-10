import { Col, Container, Row } from "react-bootstrap";

import frame_angle from '../assets/img/frame_angle.svg'
import Avatar from "./Avatar";

export default function About() {

    return (<>
        <div className="anchor" id="l-association" style={{ top: 0 }}></div>
        <Container fluid className="section d-flex flex-column justify-content-center bg-body-reverse py-4 py-lg-0">
            <div className="my-5">
                <h2 className="text-center text-body d-block  mb-5">L'association</h2>
                <Container className="mt-5">
                    <div className="position-relative">
                        <Row className="p-3 p-md-4 p-lg-5">
                            <Col xs={12} lg={6} className="mb-3 mb-lg-0 text-body">
                                {APP_CMS_category1.value}
                            </Col>
                            <Col xs={12} lg={6} className="text-body">
                                {APP_CMS_category2.value}
                            </Col>
                        </Row>

                        <img src={frame_angle} className="pe-3 pb-3 position-absolute top-0 start-0 w-25 " style={{ pointerEvents: "none", maxWidth: "100px" }} />
                        <img src={frame_angle} className="rotate-90 pe-3 pb-3 position-absolute top-0 end-0 w-25 " style={{ pointerEvents: "none", maxWidth: "100px" }} />
                        <img src={frame_angle} className="rotate-n90 pe-3 pb-3 position-absolute bottom-0 start-0 w-25 " style={{ pointerEvents: "none", maxWidth: "100px" }} />
                        <img src={frame_angle} className="rotate-180 pe-3 pb-3 position-absolute bottom-0 end-0 w-25 " style={{ pointerEvents: "none", maxWidth: "100px" }} />
                    </div>
                </Container>
            </div>
            <Container className="mt-3">
                <Row className="justify-content-evenly">
                    {APP_ABOUT_USERS.map((member, i) =>
                        <Col md={4} sm={12} key={i} className="h-100 mt-5">
                            <div className="my-3">
                                <Row className="p-3">
                                    <div className="text-center ">
                                        <div className="mb-2 text-body-reverse">
                                            <Avatar user={member} tooltip={false} />
                                        </div>
                                        <span style={{ fontSize: ".8em" }}>{member.role_asso}</span>
                                        <h5 className="fw-bold">{member.firstname} {member.lastname}</h5>
                                    </div>
                                    <Col xs={12} lg={6} className="w-100 mb-3 mb-lg-0 text-body flex-center">
                                        <p className="mt-2 text-center">
                                            {member.description}
                                        </p>
                                    </Col>
                                </Row>
                            </div>
                        </Col>
                    )}
                </Row>
            </Container>
        </Container>
    </>)
}
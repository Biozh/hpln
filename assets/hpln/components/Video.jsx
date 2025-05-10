import { Col, Container, Row } from "react-bootstrap";

export default function Video() {
    return (
        <Container className="section flex-center py-4 py-lg-0">
            <Row className="w-100 flex-center">
                <Col xs={12} className="">
                    <div className="video-container overflow-hidden rounded rounded-4 ratio-16-9 w-100">
                        <div className="glow pe-none"></div>
                    </div>
                </Col>
            </Row>
        </Container>
    )
}
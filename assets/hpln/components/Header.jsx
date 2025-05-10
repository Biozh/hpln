import { useEffect } from "react";
import { Container, Navbar, Nav, Button } from "react-bootstrap";

import logo from '../assets/img/logo.svg'
import logo_dark from '../assets/img/logo_dark.svg'
import { useTheme } from "../utils/useTheme";

export default function Header() {
  const { toggleTheme, theme } = useTheme()

  useEffect(() => {
    const container = document.querySelector('.section-container');
    if (!container) return;

    let timeout;

    const handleScroll = () => {
      const scrollTop = container.scrollTop;


      // 🔄 Mise à jour du lien actif
      document.querySelectorAll('.anchor').forEach((anchor) => {
        const anchorTop = anchor.offsetTop;
        if ((anchorTop - scrollTop) <= 160) {
          document.querySelectorAll('.nav-link').forEach((link) => {
            link.classList.remove('active');
          });

          const activeLink = document.querySelector(`.nav-link[href="#${anchor.id}"]`);
          if (activeLink) {
            activeLink.classList.add('active');
          }
        }
      });

      // 🧠 Snap au plus proche après délai
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        const scrollTop = container.scrollTop;
        const scrollBottom = scrollTop + container.clientHeight;
        const isAtBottom = scrollBottom >= container.scrollHeight - 10;
        const isMobile = window.innerWidth <= 768;

        if (isAtBottom || isMobile) return; // ✅ Vérifié au moment du scroll effectif

        const anchors = document.querySelectorAll('.anchor');

        anchors.forEach((anchor) => {
          const anchorTop = anchor.offsetTop;
          const distance = anchorTop - scrollTop - 75;

          if (Math.abs(distance) <= 250) {
            container.scrollTo({
              top: anchorTop - 75,
              behavior: 'smooth',
            });
          }
        });
      }, 100);
    };

    container.addEventListener('scroll', handleScroll);
    return () => container.removeEventListener('scroll', handleScroll);
  }, []);



  return (
    <Navbar expand={"md"} className="position-sticky top-0 bottom-0 w-100 bg-body border-bottom" style={{ zIndex: 2 }}>
      <Container className="">
        <Navbar.Brand href="#nos-projets" className="d-flex align-items-center">
          <img
            src={theme === "light" ? logo : logo_dark}
            width="48"
            height="48"
            className="d-flex align-top"
            alt="HPLN Production"
          />
          <Navbar.Text className="ms-2 fw-bold text-body">
            HPLN
          </Navbar.Text>
        </Navbar.Brand>

        <div className="d-flex">
          <Button variant="" className="me-4 d-block d-md-none " onClick={toggleTheme}><i className="fa-solid fa-xl fa-circle-half-stroke"></i></Button>
          <Navbar.Toggle />
        </div>

        <Navbar.Collapse className="justify-content-end">
          <Nav
            className="my-2 my-lg-0"
            navbarScroll
          >
            <Nav.Link className="" active href="#nos-projets">Nos projets</Nav.Link>
            <Nav.Link className="ms-md-2" href="#l-association">L'association</Nav.Link>
            <Nav.Link className="ms-md-2" href="#nous-contacter">Nous contacter</Nav.Link>
          </Nav>
        </Navbar.Collapse>
        <Button variant="" className="ms-4 d-none d-md-block " onClick={toggleTheme}><i className="fa-solid fa-xl fa-circle-half-stroke"></i></Button>
      </Container>
    </Navbar>
  )
}
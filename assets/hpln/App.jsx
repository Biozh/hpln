import { useEffect, useRef, useState } from 'react';
import { useTheme } from "./utils/useTheme";
import Container from 'react-bootstrap/Container';

import Header from './components/Header';
import About from './components/About';
import Contact from './components/Contact';
import Slider from './components/Slider';
import Footer from './components/Footer';

import logo from './assets/img/logo.svg'
import logo_dark from './assets/img/logo_dark.svg'
import Legals from './components/Legals';

function App() {
  const { theme } = useTheme()

  const [intro, setIntro] = useState(true);

  return (
    <>


      <div className={"intro bg-body d-flex flex-column justify-content-center align-items-center " + (intro ? " opacity-1" : "opacity-0 pointer-events-none")}>

        <img
          src={theme === "light" ? logo : logo_dark}
          width="86"
          height="86"
          className="d-flex align-top mb-5 animate__animated animate__fadeInDown"
          alt="HPLN"
        />

        <h1 className='text-center mb-4 animate__animated animate__fadeInDown delay-2'>
          Silence. Ça commence.
        </h1>

        <div className="animate__animated animate__fadeInDown delay-8">
          <button className='btn btn-body px-5 fs-5 mt-2' onClick={() => setIntro(false)}>Démarrer</button>
        </div>
      </div>


      <Container fluid className="bg-body bg-primary d-flex flex-column mx-0 px-0 min-vh-100" id="home">
        <Header />
        <div className="section-container" id="test">
          <Slider />
          <About />
          <Contact />

          <Footer />
        </div>
        <Legals />
      </Container>

    </>
  )
}

export default App

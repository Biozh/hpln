import { useEffect, useRef, useState } from "react";
import { Button, Col, Container, Row } from "react-bootstrap";
import { register } from 'swiper/element/bundle';
import Avatar from "./Avatar";
register();

let youtubeApiReady = false;
let youtubeReadyCallbacks = [];

function loadYouTubeAPI(callback) {
    if (youtubeApiReady) {
        callback();
    } else {
        youtubeReadyCallbacks.push(callback);
        if (!window.onYouTubeIframeAPIReady) {
            const tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            window.onYouTubeIframeAPIReady = () => {
                youtubeApiReady = true;
                youtubeReadyCallbacks.forEach(cb => cb());
                youtubeReadyCallbacks = [];
            };
        }
    }
}

function extractYouTubeID(url) {
    const regExp = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return match && match[2].length === 11 ? match[2] : null;
}

function SlideVideo({ videoId, onPlay, index, setPlayer }) {
    const containerRef = useRef(null);

    useEffect(() => {
        if (!videoId || !containerRef.current) return;

        loadYouTubeAPI(() => {
            const player = new window.YT.Player(containerRef.current, {
                videoId,
                events: {
                    onReady: (event) => {
                        setPlayer(index, event.target);
                    },
                    onStateChange: (event) => {
                        if (event.data === window.YT.PlayerState.PLAYING) {
                            onPlay?.();
                        }
                    },
                },
                playerVars: {
                    autoplay: 0,
                    mute: 1,
                    modestbranding: 1,
                    rel: 0,
                },
            });
        });
    }, [videoId, index, setPlayer]);

    return (
        <div
            style={{
                width: '100%',
                height: '50vh',
                aspectRatio: '16 / 9',
                position: 'relative',
                overflow: 'hidden',
                borderRadius: '0',
            }}
        >
            <div ref={containerRef} style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%' }} />
        </div>
    );
}

export default function Slider() {
    const slider = useRef(null);
    const playerRefs = useRef([]);
    const playerReady = useRef([]);

    const [slides] = useState(APP_VIDEOS);
    const [selectedCategory, setSelectedCategory] = useState("all");
    const [slideIndex, setSlideIndex] = useState(0);

    const filteredSlides = slides.filter(
        (slide) => selectedCategory === "all" || slide.category.id == selectedCategory
    );

    const currentSlide = filteredSlides[slideIndex] || filteredSlides[0];

    const setPlayer = (index, player) => {
        playerRefs.current[index] = player;
        playerReady.current[index] = true;
    };
    const handleSwipe = (e) => {
        const swiper = e?.detail?.[0];

        if (!swiper || typeof swiper.realIndex !== "number" || isNaN(swiper.realIndex)) {
            console.warn("🟡 Swiper emitted an invalid slide change event:", e);
            return;
        }

        const index = swiper.realIndex;
        setSlideIndex(index);

        playerRefs.current.forEach((player, i) => {
            if (
                i !== index &&
                player &&
                typeof player.pauseVideo === "function" &&
                playerReady.current[i]
            ) {
                player.pauseVideo();
            }
        });
    };


    useEffect(() => {
        if (slider.current) {
            slider.current.removeEventListener("swiperslidechange", handleSwipe);
            slider.current.addEventListener("swiperslidechange", handleSwipe);
        }
    }, []);

    useEffect(() => {
        if (slider.current?.swiper && filteredSlides.length > 0) {
            requestAnimationFrame(() => {
                slider.current.swiper.update();
                slider.current.swiper.slideTo(slideIndex);
            });
        }
    }, [slideIndex, filteredSlides.length]);


    useEffect(() => {
        setSlideIndex(0);
        playerRefs.current = [];
        playerReady.current = [];
    }, [selectedCategory]);

    const handleNext = () => {
        const next = (slideIndex + 1) % filteredSlides.length;
        setSlideIndex(next);
    };

    const handlePrev = () => {
        const prev = (slideIndex - 1 + filteredSlides.length) % filteredSlides.length;
        setSlideIndex(prev);
    };

    return (
        <>
            <div className="anchor" style={{ top: 0 }} id="nos-projets" />
            <Container className="section flex-column flex-center py-4">
                <Row className="flex-column w-100 justify-content-center">

                    {currentSlide && (
                        <Col lg={10} className="mx-auto d-flex justify-content-center align-items-center mb-3">
                            <div className="w-100 text-center">
                                <Col xs={12} className="d-flex justify-content-between align-items-center mt-5">
                                    {filteredSlides.length > 1 ? (
                                        <Button variant="link" onClick={handlePrev}>
                                            <i className="fa-solid fa-chevron-left fa-lg fs-2" />
                                        </Button>
                                    ) : (
                                        <div />
                                    )}
                                    <h1 className="m-0 text-uppercase fw-bold">{currentSlide.title}</h1>
                                    {filteredSlides.length > 1 ? (
                                        <Button variant="link" onClick={handleNext}>
                                            <i className="fa-solid fa-chevron-right fa-lg fs-2" />
                                        </Button>
                                    ) : (
                                        <div />
                                    )}
                                </Col>
                                <p
                                    className="flex-center w-100 mb-0"
                                    dangerouslySetInnerHTML={{ __html: currentSlide.description }}
                                    style={{ minHeight: "8vh" }}
                                />
                            </div>
                        </Col>
                    )}

                    <Col>
                        <select
                            className="form-select mt-4 w-auto mb-3"
                            onChange={(e) => setSelectedCategory(e.target.value)}
                            value={selectedCategory}
                        >
                            <option value="all">Toutes les catégories</option>
                            {APP_CATEGORIES.map((category, i) => (
                                <option key={i} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                    </Col>

                    <Col className="slider-picture-container mb-lg-0">
                        <div className="aa rounded rounded-4 overflow-hidden">
                            <swiper-container
                                ref={slider}
                                navigation="false"
                                loop="true"
                                speed="300"
                                effect="fade"
                                fadeEffect='{"crossFade": true}'
                            >
                                {filteredSlides.map((video, i) => (
                                    <swiper-slide key={video.id} class="mx-auto text-center">
                                        <SlideVideo
                                            videoId={extractYouTubeID(video.url)}
                                            index={i}
                                            setPlayer={setPlayer}
                                            onPlay={() => console.log("Vidéo lancée :", video.title)}
                                        />
                                    </swiper-slide>
                                ))}
                            </swiper-container>
                        </div>
                    </Col>

                    {currentSlide?.users?.length > 0 && (
                        <Col>
                            <p className="mt-4 mb-1">Membres ayant participé au projet</p>
                            <div className="d-flex flex-wrap flex-row gap-2">
                                {currentSlide.users.map((user, j) => (
                                    <Avatar key={j} user={user} />
                                ))}
                            </div>
                        </Col>
                    )}

                </Row>
            </Container>
        </>
    );
}

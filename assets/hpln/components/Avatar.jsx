import { useEffect } from "react"
import { Tooltip } from "bootstrap";

export default function Avatar({ user, tooltip = true }) {

    useEffect(() => {
        if (!tooltip) return
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))
    }, [user])

    const id = Date.now() + Math.floor(Math.random() * 1000)
    return (<>
        <div className="btn p-0 rounded-circle" data-bs-toggle="modal" data-bs-target={"#participant-" + user.id + id}>
            <div data-bs-toggle={tooltip ? "tooltip" : ""} data-bs-placement="bottom" data-bs-title={`${user.firstname} ${user.lastname}`}>
                {user.pictureName ? <>
                    <img src={`${APP_ASSETS_URL}uploads/avatars/${user.pictureName}`} alt="Avatar" className="rounded-circle bg-secondary" width={40} height={40} />
                </> : <>
                    <div className="rounded-circle bg-body-secondary" style={{ width: 40, height: 40 }}></div>
                </>}
            </div>
        </div>

        <div className="modal text-start text-body-reverse" tabIndex="-1" id={"participant-" + user.id + id}>
            <div className="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div className="modal-content">
                    <div className="modal-header">
                        {user.pictureName && <img src={`${APP_ASSETS_URL}uploads/avatars/${user.pictureName}`} alt="Avatar" className="rounded-circle bg-secondary me-2" width={64} height={64} />}
                        <div className="d-flex flex-column">
                            <h5 className="modal-title">{user.firstname} {user.lastname}</h5>
                            {user.role_asso && <h6 className="modal-subtitle mb-1">{user.role_asso}</h6>}
                            <h6 className="modal-subtitle text-muted mb-0 fs-6">{user.email}</h6>
                        </div>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    {user.description && <>
                        <div className="modal-body">
                            <p className="mb-0">{user.description}</p>
                        </div>
                    </>}
                </div>
            </div>
        </div>

    </>)
}
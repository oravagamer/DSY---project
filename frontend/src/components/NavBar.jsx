import {Link, Outlet} from "react-router-dom";

const NavBar = () => {
    return (<>
        <nav>
            <ul>
                <li><Link to="/dash">Home</Link></li>
            </ul>
        </nav>
        <Outlet />
        <footer>Foot</footer>
    </>)
}

export default NavBar;
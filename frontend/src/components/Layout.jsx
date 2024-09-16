import {Outlet, Link} from "react-router-dom";
import 'react-toastify/dist/ReactToastify.css';
import {
    ListItemText,
    ListItemIcon,
    ListItemButton,
    ListItem,
    Divider,
    List,
    Drawer,
    IconButton,
    Typography,
    Box,
    Toolbar,
    AppBar,
    Grid,
    Icon
} from '@mui/material';
import ExitToAppIcon from '@mui/icons-material/ExitToApp';
import MenuIcon from '@mui/icons-material/Menu';
import HomeIcon from '@mui/icons-material/Home';
import AddCircleIcon from '@mui/icons-material/AddCircle';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import {useState} from "react";
import Secure from "./Secure.jsx";
import oravixSecurity from "../security.js";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import LogoIcon from "./LogoIcon.jsx";
import AccessibilityIcon from '@mui/icons-material/Accessibility';
import RoleRestricted from "./RoleRestricted.jsx";

const Layout = () => {
    const [open, setOpen] = useState(false);
    const {getUserId} = useOravixSecurity();

    return (<Box sx={{height: "100vh"}}>
        <AppBar sx={{height: "64px", position: "fixed", top: 0}}>
            <Toolbar component={Grid} container>
                <Grid item xs={4} sx={{height: "100%", display: "flex", alignItems: "center"}}>
                    <Secure>
                        <IconButton
                            size="large"
                            edge="start"
                            color="inherit"
                            aria-label="menu"
                            sx={{mr: 2}}
                            onClick={() => setOpen(true)}>
                            <MenuIcon />
                        </IconButton>
                    </Secure>
                </Grid>
                <Grid item xs={4} sx={{
                    justifyContent: "space-around", display: "flex", height: "100%", alignItems: "center"
                }}>
                    <Link to="/dash/home" style={{width: "auto", height: "inherit", aspectRatio: "1/1", display: "flex", justifyContent: "center"}}><LogoIcon fontSize="large" sx={{height: "inherit"}} /></Link>
                </Grid>
                <Grid item xs={4} sx={{height: "100%", display: "flex", alignItems: "center"}}></Grid>
            </Toolbar>
        </AppBar>
        <Typography component="section" gutterBottom flexDirection="column" display="flex"
                    sx={{position: "absolute", top: "64px", bottom: "64px", width: "100%"}}
                    justifyContent="center">
            <Outlet />
        </Typography>
        <Box component="footer" sx={{
            height: "64px", display: "flex", justifyContent: "center", position: "fixed", width: "100%", bottom: 0
        }}>
            <Typography variant="body2" color="secondary" align="center"
                        sx={{placeSelf: "flex-end"}}>©
                Santos_Father</Typography>
        </Box>
        <Drawer open={open} onClose={() => setOpen(false)}>
            <Box
                sx={{width: 250}}
                role="presentation"
                onClick={() => setOpen(false)}>
                <List>
                    <ListItem>
                        <ListItemButton onClick={() => oravixSecurity.logout()}>
                            <ListItemIcon>
                                <ExitToAppIcon />
                            </ListItemIcon>
                            <ListItemText primary="Logout" />
                        </ListItemButton>
                    </ListItem>
                </List>
                <Divider />
                <Secure>
                    <List>
                        <ListItem>
                            <ListItemButton component={Link} to={`/dash/user/${getUserId()}`}>
                                <ListItemIcon>
                                    <AccountCircleIcon />
                                </ListItemIcon>
                                <ListItemText primary="Profile" />
                            </ListItemButton>
                        </ListItem>
                        <ListItem>
                            <ListItemButton component={Link} to="/dash/home">
                                <ListItemIcon>
                                    <HomeIcon />
                                </ListItemIcon>
                                <ListItemText primary="Home" />
                            </ListItemButton>
                        </ListItem>
                        <ListItem>
                            <ListItemButton component={Link} to="/dash/order/add">
                                <ListItemIcon>
                                    <AddCircleIcon />
                                </ListItemIcon>
                                <ListItemText primary="Add order" />
                            </ListItemButton>
                        </ListItem>
                    </List>
                    <RoleRestricted role="admin">
                        <List>
                            <Divider />
                            <ListItem>
                                <ListItemButton component={Link} to="/dash/roles">
                                    <ListItemIcon>
                                        <AccessibilityIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Roles" />
                                </ListItemButton>
                            </ListItem>
                            <ListItem>
                                <ListItemButton component={Link} to="/dash/roles/add">
                                    <ListItemIcon>
                                        <AddCircleIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Add role" />
                                </ListItemButton>
                            </ListItem>
                        </List>
                    </RoleRestricted>
                </Secure>
            </Box>
        </Drawer>
    </Box>)
}

export default Layout;
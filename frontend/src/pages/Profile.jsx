import {Link, useParams} from "react-router-dom";
import {backendUrl} from "../../settings.js";
import GoBack from "../components/GoBack.jsx";
import {
    Card, CardActions, CardContent, Button, Typography
} from '@mui/material';
import useOravixFetch from "../hooks/useOravixFetch.js";

const Profile = () => {
    const {id} = useParams();
    const {data} = useOravixFetch(`${backendUrl}/user?id=${id}`, {
        method: "GET"
    }, true, true, {
        username: "Loading", first_name: "Loading", last_name: "Loading", email: "Loading", roles: ["Loading"]
    });
    return (<Card sx={{minWidth: "300px", alignSelf: "center", borderRadius: "5px"}} variant="outlined">
        <CardContent sx={{display: "flex", flexDirection: "column"}}>
            <Typography variant="h4" component="div">{data.username}</Typography>
            <Typography component="h6">First name: <Typography sx={{fontSize: 14}} color="text.secondary"
                                                               component="div">{data.first_name}</Typography></Typography>
            <Typography component="h6">Last name: <Typography sx={{fontSize: 14}} color="text.secondary"
                                                              component="div">{data.last_name}</Typography></Typography>
            <Typography component="h6">Email: <Typography sx={{fontSize: 14}} color="text.secondary"
                                                          component="div">{data.email}</Typography></Typography>
            <Typography variant="body2">Roles:</Typography>
            {data?.roles.map && data?.roles.map(role => <Typography key={role} sx={{fontSize: 14, pl: 2}}
                                                                    color="text.secondary">{role}</Typography>)}
        </CardContent>
        <CardActions sx={{justifyContent: "space-between"}}>
            <GoBack />
            <Button component={Link} to="edit">Edit</Button>
        </CardActions>
    </Card>)
}

export default Profile;
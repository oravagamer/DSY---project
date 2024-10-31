import {
    Card, CardActions, CardContent, Typography, Box, Button
} from '@mui/material';
import GoBack from "../components/GoBack.jsx";
import {useRouteError, Link} from "react-router-dom";

const ErrorPage = () => {
    const error = useRouteError();
    return (<Box sx={{width: "100vw", height: "100vh", display: "flex", justifyContent: "space-around"}}>
        <Card sx={{width: "350px", alignSelf: "center"}}>
            <CardContent sx={{
                display: "flex",
                alignItems: "center",
                flexDirection: "column",
                '& .MuiTextField-root, & .MuiButton-root, & .MuiFormControl-root': {
                    m: 1
                }
            }}>
                <Typography variant="h1">Oops!</Typography>
                <Typography variant="h2">{error.status}</Typography>
                <Typography component="p" variant="p">{error.statusText}</Typography>
                {error.data?.message && <Typography>{error.data.message}</Typography>}
            </CardContent>
            <CardActions sx={{justifyContent: "space-between"}}>
                    <GoBack />
                    <Button component={Link} to="/dash/home">Home</Button>
            </CardActions>
        </Card>
    </Box>)
}

export default ErrorPage;
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from "@mui/material/Typography";

const EmailWait = () => {
    return (
        <Card sx={{width: "350px", height: "max-content", alignSelf: "center", borderRadius: "25px"}}>
            <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                <Typography gutterBottom variant="h3" component="div">Email was sent</Typography>
                <Typography gutterBottom>Please wait for an email and then follow the link in email to finish action.</Typography>
            </CardContent>
        </Card>
    );
}

export default EmailWait;
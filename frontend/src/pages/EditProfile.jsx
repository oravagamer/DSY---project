import GoBack from "../components/GoBack.jsx";
import {useNavigate, useParams} from "react-router-dom";
import {backendUrl} from "../../settings.js";
import {useEffect, useState} from "react";
import useOravixFetch from "../hooks/useOravixFetch.js";
import {
    Card, CardActions, CardContent, Button, TextField, ButtonGroup
} from '@mui/material';
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const EditProfile = () => {
    const {id} = useParams();
    const [username, setUsername] = useState("Loading");
    const [usernameValid, setUsernameValid] = useState("");
    const [usernameFocus, setUsernameFocus] = useState(false);
    const [firstName, setFirstName] = useState("Loading");
    const [firstNameValid, setFirstNameValid] = useState("");
    const [firstNameFocus, setFirstNameFocus] = useState(false);
    const [lastName, setLastName] = useState("Loading");
    const [lastNameValid, setLastNameValid] = useState("");
    const [lastNameFocus, setLastNameFocus] = useState(false);
    const navigate = useNavigate();
    const {security} = useOravixSecurity();
    const {data, loading, status} = useOravixFetch(`${backendUrl}/user?id=${id}`, {
        method: "GET"
    }, true, true, {
        username: "Loading", first_name: "Loading", last_name: "Loading", email: "Loading", roles: ["Loading"]
    });
    const saveChanges = event => {
        event.preventDefault();
        security.secureEncryptedFetch(`${backendUrl}/user?id=${id}`, {
            headers: {
                "Content-type": "application/json"
            }, method: "PUT", body: JSON.stringify({
                username: username, first_name: firstName, last_name: lastName
            })
        }).then(res => {
            setUsernameFocus(true);
            setUsernameValid("Username already used");
        })
    }
    const deleteUser = () => {
        security.secureEncryptedFetch(`${backendUrl}/user?id=${id}`, {
            method: "DELETE"
        })
            .then(async res => {
                if (await res.status < 400) {
                    await navigate("/dash/home")
                }
            });
    }

    useEffect(() => {
        if (status === 404) {
            navigate("/dash/home")
        }
        setUsername(data.username);
        setFirstName(data.first_name);
        setLastName(data.last_name);
    }, [loading]);

    return (
        <Card component="form" onSubmit={saveChanges} sx={{width: "350px", height: "max-content", alignSelf: "center"}}>
            <CardContent sx={{
                display: "flex",
                flexDirection: "column",
                '& .MuiTextField-root, & .MuiButton-root, & .MuiFormControl-root': {
                    m: 1
                }
            }}>
                <TextField
                    variant="filled"
                    label={usernameValid !== "" ? usernameValid : "Username"}
                    color={usernameValid !== "" ? "error" : "primary"}
                    value={username}
                    onChange={(event) => {
                        setUsername(event.target.value);
                        setUsernameFocus(false);
                        setUsernameValid("");
                    }}
                    inputProps={{minLength: 3}}
                    focused={usernameFocus || usernameValid !== ""}
                    onFocus={() => setUsernameFocus(true)}
                    onBlur={() => setUsernameFocus(false)}
                    onInvalid={event => {
                        event.preventDefault();
                        setUsernameValid(event.target.value.length < 3 ? "Min 3 chars" : "");
                    }}
                    autoComplete="username" />
                <TextField
                    required
                    variant="filled"
                    label={firstNameValid !== "" ? firstNameValid : "First name"}
                    color={firstNameValid !== "" ? "error" : "primary"}
                    value={firstName}
                    onChange={(event) => {
                        setFirstName(event.target.value);
                        setFirstNameFocus(false);
                        setFirstNameValid("");
                    }}
                    inputProps={{pattern: "([a-zA-Z]+){3,255}"}}
                    focused={firstNameFocus || firstNameValid !== ""}
                    onFocus={() => setFirstNameFocus(true)}
                    onBlur={() => setFirstNameFocus(false)}
                    onInvalid={event => {
                        event.preventDefault();
                        if (event.target.value.length < 3) {
                            setFirstNameValid("Min 3 chars");
                        } else if (event.target.value.length > 255) {
                            setFirstNameValid("Max 255 chars");
                        } else if (event.target.value.matchAll("[a-zA-Z]+") !== null) {
                            setFirstNameValid("Type only a-z and A-Z")
                        }
                    }}
                    autoComplete="given-name" />
                <TextField
                    required
                    variant="filled"
                    label={lastNameValid !== "" ? lastNameValid : "Last name"}
                    color={lastNameValid !== "" ? "error" : "primary"}
                    value={lastName}
                    onChange={(event) => {
                        setLastName(event.target.value);
                        setLastNameFocus(false);
                        setLastNameValid("");
                    }}
                    inputProps={{pattern: "([a-zA-Z]+){3,255}"}}
                    focused={lastNameFocus || lastNameValid !== ""}
                    onFocus={() => setLastNameFocus(true)}
                    onBlur={() => setLastNameFocus(false)}
                    onInvalid={event => {
                        event.preventDefault();
                        if (event.target.value.length < 3) {
                            setLastNameValid("Min 3 chars");
                        } else if (event.target.value.length > 255) {
                            setLastNameValid("Max 255 chars");
                        } else if (event.target.value.matchAll("[a-zA-Z]+") !== null) {
                            setLastNameValid("Type only a-z and A-Z")
                        }
                    }}
                    autoComplete="family-name" />
                <Button variant="outlined">Change email</Button>
                <Button variant="outlined">Change password</Button>
                <Button variant="outlined" color="warning">Edit roles</Button>
            </CardContent>
            <CardActions>
                <GoBack />
                <ButtonGroup variant="contained">
                    <Button color="success" type="submit">Save changes</Button>
                    <Button color="error" onClick={deleteUser}>Delete</Button>
                </ButtonGroup>
            </CardActions>
            {/*<img src="/user.svg" />*/}
        </Card>)
}

export default EditProfile;
import {useState, useEffect} from 'react';
import IconButton from '@mui/material/IconButton';
import FilledInput from '@mui/material/FilledInput';
import InputLabel from '@mui/material/InputLabel';
import InputAdornment from '@mui/material/InputAdornment';
import FormControl from '@mui/material/FormControl';
import Visibility from '@mui/icons-material/Visibility';
import VisibilityOff from '@mui/icons-material/VisibilityOff';
import "./PasswordInput.css";

const PasswordInput = ({setPassword, password, autoComplete, sx = {}, required = false, message = ""}) => {
    const [showPassword, setShowPassword] = useState(false);
    const [validText, setValidText] = useState("");
    const [focus, setFocus] = useState(false);

    useEffect(() => {
        setValidText(message)
    }, [message])

    const handleClickShowPassword = () => setShowPassword((show) => !show);

    const handleMouseDownPassword = (event) => {
        event.preventDefault();
    };
    return (<FormControl sx={sx} variant="filled" required={required} focused={focus || validText !== ""}
                         color={validText !== "" ? "error" : "primary"}>
        <InputLabel htmlFor="filled-adornment-password">{validText !== "" ? validText : "Password"}</InputLabel>
        <FilledInput
            id="filled-adornment-password"
            type={showPassword ? 'text' : 'password'}
            className="password-input"
            value={password}
            onChange={event => {
                setPassword(event.target.value);
                setValidText("");
                setFocus(false);
            }}
            onFocus={() => setFocus(true)}
            onBlur={() => setFocus(false)}
            onInvalid={event => {
                event.preventDefault();
                const value = event.target.value;
                if (value.length < 8) {
                    setValidText("Min 8 chars");
                } else if (value.length > 16) {
                    setValidText("Max 16 chars");
                } else if (value.match(new RegExp("[a-z]")) == null) {
                    setValidText("At least 1 Lowercase");
                } else if (value.match(new RegExp("[A-Z]")) === null) {
                    setValidText("At least 1 Uppercase");
                } else if (value.match(new RegExp("[0-9]")) === null) {
                    setValidText("At least 1 Number");
                } else if (value.match(new RegExp("[!@#$%^&*_=+-]")) === null) {
                    setValidText("At least 1 Symbol !@#$%^&*_=+-");
                }
            }}
            autoComplete={autoComplete}
            inputProps={{pattern: "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\\W).{8,16}$"}}
            endAdornment={<InputAdornment position="end">
                <IconButton
                    aria-label="toggle password visibility"
                    onClick={handleClickShowPassword}
                    onMouseDown={handleMouseDownPassword}
                    edge="end"
                >
                    {showPassword ? <VisibilityOff /> : <Visibility />}
                </IconButton>
            </InputAdornment>}
        />
    </FormControl>)
}

export default PasswordInput;
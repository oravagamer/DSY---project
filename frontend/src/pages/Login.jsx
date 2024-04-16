function Login() {
    return (<form method="GET" target="_self" action="./dash">
        <input type="text" placeholder="Username" />
        <input type="password" placeholder="Password" />
        <input type="submit" value="Login"/>
    </form>)
}

export default Login;
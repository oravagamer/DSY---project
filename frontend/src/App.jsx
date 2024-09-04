import React from 'react'
import {createBrowserRouter, RouterProvider, Outlet} from "react-router-dom";
import Login from "./pages/Login.jsx";
import Layout from "./components/Layout.jsx";
import Home from "./pages/Home.jsx";
import Order from "./pages/Order.jsx";
import EditOrder from "./pages/EditOrder.jsx";
import AddOrder from "./pages/AddOrder.jsx";
import EditProfile from "./pages/EditProfile.jsx";
import Secure from "./components/Secure.jsx";
import UnSecure from "./components/UnSecure.jsx";
import Profile from "./pages/Profile.jsx";
import Users from "./pages/Users.jsx";
import ErrorPage from "./pages/ErrorPage.jsx";
import Register from "./pages/Register.jsx";
import Redirect from "./pages/Redirect.jsx";
import EmailWait from "./pages/EmailWait.jsx";
import RoleRestricted from "./components/RoleRestricted.jsx";
import Roles from "./pages/Roles.jsx";
import EditRole from "./pages/EditRole.jsx";

const App = () => {
    const router = createBrowserRouter([{
            path: "/",
            errorElement: <ErrorPage />,
            element: <Layout />,
            children:
                [
                    {
                        path: "login",
                        element: <UnSecure><Login /></UnSecure>
                    },
                    {
                        path: "register",
                        element: <UnSecure><Register /></UnSecure>
                    },
                    {
                        path: "redirect",
                        element: <Redirect />
                    },
                    {
                        path: "email-wait",
                        element: <EmailWait />
                    },
                    {
                        path: "dash",
                        element: <Secure redirect={true}><Outlet /></Secure>,
                        children: [
                            {
                                path: "home",
                                element: <Home />
                            },
                            {
                                path: "roles",
                                element: <RoleRestricted role="admin"><Outlet /></RoleRestricted>,
                                children: [
                                    {
                                        index: true,
                                        element: <Roles />
                                    },
                                    {
                                        path: ":id",
                                        element: <EditRole />
                                    }
                                ]
                            },
                            {
                                path: "user",
                                children: [
                                    {
                                        path: ":id",
                                        children: [
                                            {
                                                index: true,
                                                element: <Profile />
                                            },
                                            {
                                                path: "edit",
                                                element: <EditProfile />
                                            }
                                        ]
                                    },
                                    {
                                        index: true,
                                        element: <Users />
                                    }
                                ]
                            },
                            {
                                path: "order",
                                children: [
                                    {
                                        path: "add",
                                        element: <AddOrder />
                                    },
                                    {
                                        path: ":id",
                                        children: [
                                            {
                                                index: true,
                                                element: <Order />
                                            },
                                            {
                                                path: "edit",
                                                element: <EditOrder />
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
        }])
    ;
    return (
        <RouterProvider router={router} />
    )
}

export default App

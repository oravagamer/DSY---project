import React from 'react'
import {createBrowserRouter, RouterProvider} from "react-router-dom";
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
import RoleRestricted from "./components/RoleRestricted.jsx";

const App = () => {
    const router = createBrowserRouter([{
            path: "/",
            errorElement: <ErrorPage />,
            children:
                [
                    {
                        index: true,
                        element: <UnSecure><Login /></UnSecure>
                    },
                    {
                        path: "dash",
                        element: <Secure><Layout /></Secure>,
                        children: [
                            {
                                path: "home",
                                element: <Home />
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
                                        path: "all",
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

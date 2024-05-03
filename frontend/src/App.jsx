import React from 'react'
import {createBrowserRouter, RouterProvider} from "react-router-dom";
import Login from "./pages/Login.jsx";
import NavBar from "./components/NavBar.jsx";
import Home from "./pages/Home.jsx";
import Order from "./pages/Order.jsx";
import EditOrder from "./pages/EditOrder.jsx";
import AddOrder from "./pages/AddOrder.jsx";
import Profile from "./pages/Profile.jsx";
import EditProfile from "./pages/EditProfile.jsx";
import Secure from "./components/Secure.jsx";
import UnSecure from "./components/UnSecure.jsx";

const App = () => {
    const router = createBrowserRouter([
        {
            index: true,
            element: <UnSecure><Login/></UnSecure>,
        },
        {
            path: "dash",
            element: <Secure><NavBar/></Secure>,
            children: [
                {
                    index: true,
                    element: <Home/>
                },
                {
                    path: "user/:id",
                    children: [
                        {
                            index: true,
                            element: <Profile/>
                        },
                        {
                            path: "edit",
                            element: <EditProfile/>
                        }
                    ]
                },
                {
                    path: "order",
                    children: [
                        {
                            path: "add",
                            element: <AddOrder/>
                        },
                        {
                            path: ":id",
                            children: [
                                {
                                    index: true,
                                    element: <Order/>
                                },
                                {
                                    path: "edit",
                                    element: <EditOrder/>
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]);
    return (
        <RouterProvider router={router}/>
    )
}

export default App

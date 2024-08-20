import {useEffect, useState} from "react";
import {backendUrl} from "../../settings.js";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import {
    Typography,
    Dialog,
    DialogTitle,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TablePagination,
    TableRow,
    Button,
    TableSortLabel
} from "@mui/material";
import useOravixFetch from "../hooks/useOravixFetch.js";

const UsersSelect = ({user, setUser}) => {
    const [open, setOpen] = useState(false);
    const {security} = useOravixSecurity();
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);
    const [sorting, setSorting] = useState({
        sortBy: "username", asc: true
    });
    const {
        data, refetch
    } = useOravixFetch(`${backendUrl}/users?page=${page}&count=${rowsPerPage}&sort-by=${sorting.sortBy}&asc=${+sorting.asc}`, {
        method: "GET"
    }, true, true, []);
    const [userData, setUserData] = useState();

    const setRef = data => {
        setUser(data?.id);
        setUserData(data);
    }

    const handleChangePage = (event, newPage) => {
        setPage(newPage);
        refetch();
    };
    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(+event.target.value);
        setPage(0);
        refetch();
    };

    const selectUser = () => {
        refetch();
        setOpen(true);
    };

    const closeSelectUser = (input_user) => {
        setRef(input_user);
        setOpen(false);
    }

    useEffect(() => {
        if (user !== undefined) {
            security.secureEncryptedFetch(`${backendUrl}/user?id=${user}`, {
                method: "GET"
            })
                .then(async res => {
                    setRef({
                        id: user, ...JSON.parse(res.body)
                    });

                })
        }
    }, [user]);
    return (<>
        <Button variant="outlined" onClick={selectUser}>
            {`Select user Selected user: ${userData === undefined || user === null ? "None" : userData.username}`}
        </Button>
        <Dialog
            open={open}
            onClose={() => closeSelectUser()}
            fullWidth
        >
            <DialogTitle>Select user</DialogTitle>
            <TableContainer sx={{height: "100vh", width: "100%"}}>
                <Table
                    stickyHeader>
                    <TableHead>
                        <TableRow>
                            {["username", "first_name", "last_name", "email"].map(value => <TableCell key={value}>
                                <TableSortLabel
                                    active={sorting.sortBy === value}
                                    direction={sorting.asc ? "asc" : "desc"}
                                    onClick={() => {
                                        setSorting({
                                            sortBy: value, asc: sorting.sortBy === value ? !sorting.asc : sorting.asc
                                        });
                                        refetch();
                                    }}
                                >
                                    {value
                                        .split("_")
                                        .map(value => value.charAt(0).toUpperCase() + value.slice(1))
                                        .join(" ")}
                                </TableSortLabel>
                            </TableCell>)}

                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {data?.users?.map && data?.users?.map(value => <TableRow
                            key={value.id}
                            onClick={() => closeSelectUser(value)}
                            hover>
                            <TableCell>{value.username}</TableCell>
                            <TableCell>{value.first_name}</TableCell>
                            <TableCell>{value.last_name}</TableCell>
                            <TableCell>{value.email}</TableCell>
                        </TableRow>)}
                    </TableBody>
                </Table>
            </TableContainer>
            <TablePagination
                rowsPerPageOptions={[10, 25, 100]}
                component="div"
                count={data?.count === undefined ? 0 : data?.count}
                rowsPerPage={rowsPerPage}
                sx={{overflow: "hidden"}}
                page={page}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage} />
        </Dialog>
    </>);
}


export default UsersSelect;
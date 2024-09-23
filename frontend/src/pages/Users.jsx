import {backendUrl} from "../../settings.js";
import {Link} from "react-router-dom";
import {useState, useEffect} from "react";
import {
    IconButton,
    FormControl,
    Input,
    InputAdornment,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableSortLabel,
    TableHead,
    TablePagination,
    TableRow,
    Divider
} from '@mui/material';
import useOravixFetch from "../hooks/useOravixFetch.js";
import SearchIcon from '@mui/icons-material/Search';

const Home = () => {
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);
    const [sorting, setSorting] = useState({
        sortBy: "username", asc: false
    });
    const {
        data, refetch, status
    } = useOravixFetch(`${backendUrl}/users/?page=${page}&count=${rowsPerPage}&sort-by=${sorting.sortBy}&asc=${+sorting.asc}`, {
        method: "GET"
    }, true, true, []);

    const handleChangePage = (event, newPage) => {
        setPage(newPage);
        refetch();
    };
    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(+event.target.value);
        setPage(0);
        refetch();
    };

    return (<>
        <FormControl sx={{m: "10px"}}>
            <Input
                id="table-search"
                type="text"
                placeholder="Search {in:<Column> <Value>}"
                endAdornment={<InputAdornment position="end">
                    <IconButton
                        edge="end"
                    >
                        <SearchIcon />
                    </IconButton>
                </InputAdornment>} />
        </FormControl>
        <Divider />
        <TableContainer
            sx={{width: "100%", flexGrow: 1, height: "100%"}}>
            <Table
                stickyHeader>
                <TableHead>
                    <TableRow>
                        {["username", "email", "active"].map(value => <TableCell key={value}>
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
                        <TableCell>Action</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {data?.users?.map && data?.users?.map(value => {
                        return (<TableRow
                            key={value.id}
                            hover>
                            <TableCell>{value.username}</TableCell>
                            <TableCell>{value.email}</TableCell>
                            <TableCell>{value.active}</TableCell>
                            <TableCell><Link to={`./${value.id}`}>Info</Link></TableCell>
                        </TableRow>)
                    })}
                </TableBody>
            </Table>
        </TableContainer>
        <TablePagination
            rowsPerPageOptions={[10, 25, 100]}
            component="div"
            count={data.count === undefined ? 0 : data.count}
            rowsPerPage={rowsPerPage}
            sx={{overflow: "hidden"}}
            page={page}
            onPageChange={handleChangePage}
            onRowsPerPageChange={handleChangeRowsPerPage} />
    </>)
}

export default Home;
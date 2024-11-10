import * as React from "react";
import {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from "@tanstack/react-table";
import axios from 'axios';
import { Input } from "@/components/ui/input";
import { MoreHorizontal, ArrowUpDown  } from "lucide-react"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { DashboardLayout } from "@/Layouts/Dashboard";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
  } from "@/components/ui/dropdown-menu"

interface DataRow {
    Id: string; // Required field
    [key: string]: string | number | undefined; // Optional fields
}

const DataTable: React.FC = () => {
    const [tables, setTables] = React.useState<string[]>([]);
    const [data, setData] = React.useState<DataRow[]>([]);
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});
    const [newRow, setNewRow] = React.useState<DataRow>({ Id: '' });
    const [currentTable, setCurrentTable] = React.useState<string>('');
    const [editRowId, setEditRowId] = React.useState<string | null>(null);
    const [editData, setEditData] = React.useState<DataRow | null>(null);

    React.useEffect(() => {
        axios.get('/api/data')
            .then(response => {
                setTables(response.data);
            })
            .catch(error => {
                console.error("Error fetching tables:", error);
            });
    }, []);

    const fetchData = (table: string) => {
        if (table) {
            axios.get(`/api/data/${table}`)
                .then(response => {
                    setData(response.data);
                    setCurrentTable(table);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                });
        } else {
            setData([]);
        }
    };

    const columns: ColumnDef<DataRow>[] = React.useMemo(() => {
        if (data.length > 0) {
            return Object.keys(data[0]).map(key => ({
                accessorKey: key,
                header: key.charAt(0).toUpperCase() + key.slice(1),
                cell: ({ row }) => <div>{row.getValue(key)}</div>,
            }));
        }
        return [];
    }, [data]);

    const table = useReactTable({
        data,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
        },
    });

    // Handle adding new row to the database
    const handleAddRow = () => {
        const newRowData: DataRow = { ...newRow };
        axios.post(`/api/data/${currentTable}`, newRowData)
            .then(response => {
                setData(prev => [...prev, response.data]);
            })
            .catch(error => {
                console.error("Error adding row:", error);
            });
    };

    const handleEditClick = (row: DataRow) => {
        setEditRowId(row.Id);
        setEditData(row); // Load row data for editing
    };

    const handleSaveClick = () => {
        if (editData) {
            axios.put(`/api/data/${currentTable}/${editData.Id}`, editData)
                .then(response => {
                    setEditRowId(null);
                    setEditData(null); // Clear edit state
                })
                .catch(error => {
                    console.error("Error saving row:", error);
                });
        }
    };

    const handleInputChange = (key: string, value: string | number) => {
        if (editRowId) {
            setEditData(prev => prev ? { ...prev, [key]: value } : null);
        } else {
            setNewRow(prev => ({ ...prev, [key]: value }));
        }
    };    

    const handleDeleteRow = (id: string, table: string) => {
        axios.delete(`/api/data/${table}/${id}`)
            .then(() => {
                setData(prev => prev.filter(item => item.id !== id));
            })
            .catch(error => {
                console.error("Error deleting row:", error);
            });
    };

    return (
        <DashboardLayout>
            <div>
                <h1 className="text-3xl font-bold text-gray-800 mb-6">Data</h1>
                <div>
                    <label htmlFor="tables" className="block text-sm font-medium text-gray-700 mb-2">
                        Select Table
                    </label>
                    <select
                        id="tables"
                        onChange={(e) => fetchData(e.target.value)}
                        className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mb-4"
                    >
                        <option value="">-- Select a Table --</option>
                        {tables.map((table) => (
                            <option key={table} value={table}>
                                {table}
                            </option>
                        ))}
                    </select>
                    <div className="flex space-x-4 mb-4">
                        <Button onClick={() => fetchData(currentTable)}>Refresh</Button>
                        <div>
                            <label htmlFor="value" className="block text-sm font-medium text-gray-700 mb-2">Contain</label>
                        </div>
                        <Input
                            id="query"
                            onChange={(e) => setColumnFilters((prev) => prev.map(filter => ({ ...filter, value: e.target.value })))}
                            className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Enter query"
                        />
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                            <Button variant="outline" className="ml-auto">
                                Columns
                            </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {table
                                .getAllColumns()
                                .filter(
                                    (column) => column.getCanHide()
                                )
                                .map((column) => {
                                    return (
                                    <DropdownMenuCheckboxItem
                                        key={column.id}
                                        className="capitalize"
                                        checked={column.getIsVisible()}
                                        onCheckedChange={(value) =>
                                        column.toggleVisibility(!!value)
                                        }
                                    >
                                        {column.id}
                                    </DropdownMenuCheckboxItem>
                                    )
                                })}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div> 
                    </div>

                {/* Table */}
                <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map(headerGroup => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map(header => (
                                    <TableHead key={header.id}>
                                        <Button
                                            variant="ghost"
                                            onClick={() => header.column.toggleSorting(header.column.getIsSorted() === "asc")}
                                        >
                                            {flexRender(header.column.columnDef.header, header.getContext())}
                                            <ArrowUpDown className="ml-2 h-4 w-4" />
                                        </Button>
                                    </TableHead>
                                ))}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                            {table.getRowModel().rows.length ? (
                                table.getRowModel().rows.map(row => (
                                    <TableRow key={row.original.Id}>
                                        {row.getVisibleCells().map(cell => (
                                            <TableCell key={cell.id}>
                                                {editRowId === row.original.Id && cell.column.id !== 'Id' ? (
                                                    <Input
                                                        className={cell.column.id}
                                                        value={editData ? editData[cell.column.id as keyof DataRow] || '' : ''}
                                                        onChange={(e) => handleInputChange(cell.column.id, e.target.value)}
                                                    />
                                                ) : (
                                                    flexRender(cell.column.columnDef.cell, cell.getContext())
                                                )}
                                            </TableCell>
                                        ))}
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" className="h-8 w-8 p-0">
                                                    <span className="sr-only">Open menu</span>
                                                    <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                    {editRowId === row.original.Id ? (
                                                        <DropdownMenuItem onClick={handleSaveClick}>Save</DropdownMenuItem>
                                                    ) : (
                                                        <DropdownMenuItem onClick={() => { handleEditClick(row.original)}}>Edit</DropdownMenuItem>
                                                    )}
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                    onClick={() => handleDeleteRow(row.original.Id, currentTable)}
                                                    >
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={columns.length}>No data available.</TableCell>
                                </TableRow>
                            )}

                            {/* Row for adding new data */}
                            {table.getHeaderGroups().map(headerGroup => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map(header => (
                                    <TableCell key={header.id}>
                                        {header.id !== 'Id' ? (
                                            <Input
                                                className={header.id}
                                                value={newRow[header.id as keyof DataRow] || ''}
                                                onChange={(e) => handleInputChange(header.id as string, e.target.value)}
                                            />
                                        ) : <TableCell><Button onClick={handleAddRow}>Add Row</Button></TableCell> }
                                    </TableCell>
                                ))}
                                <TableCell></TableCell>
                            </TableRow>
                            ))}
                            <TableRow>
                                <TableCell>
                                    <div className="flex items-center justify-end space-x-2 py-4">
                                        <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => table.previousPage()}
                                        disabled={!table.getCanPreviousPage()}
                                        >
                                        Previous
                                        </Button>
                                        <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => table.nextPage()}
                                        disabled={!table.getCanNextPage()}
                                        >
                                        Next
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                </Table>
            </div>
            </div>  
        </DashboardLayout>
    );
};

export default DataTable;
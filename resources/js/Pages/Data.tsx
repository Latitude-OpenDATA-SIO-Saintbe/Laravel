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
import { toast } from 'sonner';
import { Toaster } from "@/components/ui/sonner"


interface DataRow {
    Id: string; // Required field
    [key: string]: string | number | undefined; // Optional fields
}

/**
 * DataTable component is a React functional component that renders a data table with various functionalities
 * such as fetching data from an API, sorting, filtering, column visibility toggling, row selection, adding new rows,
 * editing existing rows, and deleting rows. It uses the `useReactTable` hook for table management and `axios` for
 * API requests. The component also provides a dropdown menu for column visibility and actions like edit and delete
 * for each row.
 *
 * @component
 * @example
 * return (
 *   <DataTable />
 * )
 *
 * @returns {JSX.Element} The rendered DataTable component.
 *
 * @typedef {Object} DataRow
 * @property {string} Id - The unique identifier for the data row.
 *
 * @typedef {Object} SortingState
 * @property {string} id - The column id to sort by.
 * @property {string} desc - The sort direction (ascending or descending).
 *
 * @typedef {Object} ColumnFiltersState
 * @property {string} id - The column id to filter by.
 * @property {string} value - The filter value.
 *
 * @typedef {Object} VisibilityState
 * @property {boolean} [columnId] - The visibility state of a column.
 *
 * @typedef {Object} ColumnDef
 * @property {string} accessorKey - The key to access the column data.
 * @property {string} header - The header text for the column.
 * @property {function} cell - The cell rendering function.
 *
 * @typedef {Object} TableInstance
 * @property {function} getHeaderGroups - Function to get header groups.
 * @property {function} getRowModel - Function to get row model.
 * @property {function} getAllColumns - Function to get all columns.
 * @property {function} previousPage - Function to go to the previous page.
 * @property {function} nextPage - Function to go to the next page.
 * @property {boolean} getCanPreviousPage - Boolean indicating if previous page is available.
 * @property {boolean} getCanNextPage - Boolean indicating if next page is available.
 *
 * @typedef {Object} ButtonProps
 * @property {function} onClick - The click event handler.
 * @property {string} [variant] - The button variant.
 * @property {string} [size] - The button size.
 * @property {boolean} [disabled] - The disabled state of the button.
 *
 * @typedef {Object} InputProps
 * @property {string} id - The input id.
 * @property {function} onChange - The change event handler.
 * @property {string} [className] - The input class name.
 * @property {string} [placeholder] - The input placeholder.
 *
 * @typedef {Object} DropdownMenuProps
 * @property {function} asChild - The child component.
 * @property {function} align - The alignment of the dropdown menu.
 *
 * @typedef {Object} DropdownMenuItemProps
 * @property {function} onClick - The click event handler.
 *
 * @typedef {Object} DropdownMenuCheckboxItemProps
 * @property {boolean} checked - The checked state of the checkbox item.
 * @property {function} onCheckedChange - The change event handler for the checkbox item.
 *
 * @typedef {Object} DropdownMenuLabelProps
 * @property {string} children - The label text.
 *
 * @typedef {Object} DropdownMenuSeparatorProps
 *
 * @typedef {Object} TableProps
 * @property {function} children - The table children components.
 *
 * @typedef {Object} TableHeaderProps
 * @property {function} children - The table header children components.
 *
 * @typedef {Object} TableRowProps
 * @property {function} children - The table row children components.
 *
 * @typedef {Object} TableHeadProps
 * @property {function} children - The table head children components.
 *
 * @typedef {Object} TableBodyProps
 * @property {function} children - The table body children components.
 *
 * @typedef {Object} TableCellProps
 * @property {function} children - The table cell children components.
 *
 * @typedef {Object} DashboardLayoutProps
 * @property {function} children - The dashboard layout children components.
 *
 * @typedef {Object} ToasterProps
 */
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

    // Fetch
    React.useEffect(() => {
        axios.get('/api/data')
            .then(response => {
                // Set tables from the response
                setTables(response.data);
                toast.success('Tables fetched successfully!');
            })
            .catch(error => {
                console.error("Error fetching tables:", error);
                toast.error(`Error fetching tables: ${error.response?.status || 'Unknown'} - ${error.message}`);
            });
    }, []);

    // Utility function to normalize column names
    const normalizeData = (data: DataRow[]): DataRow[] => {
        return data.map(row => {
            const normalizedRow: DataRow = { Id: '' };
            for (const key in row) {
                // Check if the field is `id`, normalize it to `Id`
                normalizedRow[key === 'id' ? 'Id' : key] = row[key];
            }
            return normalizedRow;
        });
    };

    // Fetch data for the selected table
    const fetchData = (table: string) => {
        if (table) {
            axios.get(`/api/data/${table}`)
                .then(response => {
                    // Normalize the data
                    const normalizedData = normalizeData(response.data);
                    // Set data for the selected table
                    setData(normalizedData);
                    setCurrentTable(table);
                    toast.success(`Data for table "${table}" fetched successfully!`);
                    // console log response.data
                    console.log(normalizedData);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    toast.error(`Error fetching data: ${error.response?.status || 'Unknown'} - ${error.message}`);
                });
        } else {
            setData([]);
            toast.warning('No table selected, data cleared.');
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
                // Add new row to the table
                setData(prev => [...prev, response.data]);
                toast.success(`Row added successfully with ID: ${response.data.Id}`);
            })
            .catch(error => {
                console.error("Error adding row:", error);
                toast.error(`Error adding row: ${error.response?.status || 'Unknown'} - ${error.message}`);
            });
    };

    // Handle editing existing row in the database
    const handleEditClick = (row: DataRow) => {
        setEditRowId(row.Id);
        setEditData(row); // Load row data for editing
    };

    // Handle saving edited row to the database
    const handleSaveClick = () => {
        if (editData) {
            // Update row in the database
            axios.put(`/api/data/${currentTable}/${editData.Id}`, editData)
                .then(response => {
                    setEditRowId(null);
                    setEditData(null); // Clear edit state
                    toast.success('Row updated successfully!');
                })
                .catch(error => {
                    console.error("Error saving row:", error);
                    toast.error(`Error saving row: ${error.response?.status || 'Unknown'} - ${error.message}`);
                });
        }
    };

    // Handle input change for adding new row or editing existing
    const handleInputChange = (key: string, value: string | number) => {
        if (editRowId) {
            // Update edit data
            setEditData(prev => prev ? { ...prev, [key]: value } : null);
        } else {
            // Update new row data
            setNewRow(prev => ({ ...prev, [key]: value }));
        }
    };

    // Handle deleting row from the database
    const handleDeleteRow = (id: string, table: string) => {
        // Delete row from the database
        axios.delete(`/api/data/${table}/${id}`)
            .then(() => {
                // Remove row from the table
                setData(prev => prev.filter(item => item.id !== id));
                toast.success(`Row with ID ${id} deleted successfully.`);
            })
            .catch(error => {
                console.error("Error deleting row:", error);
                toast.error(`Error deleting row: ${error.response?.status || 'Unknown'} - ${error.message}`);
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
                        <div className="flex space-x-2 w-full">
                            <select
                                onChange={(e) => setColumnFilters([{ id: e.target.value, value: '' }])}
                                className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Select a Column --</option>
                                {table
                                    .getAllColumns()
                                    .filter((column) => column.getCanHide())
                                    .map((column) => (
                                        <option key={column.id} value={column.id}>
                                            {column.id}
                                        </option>
                                    ))}
                            </select>
                            <Input
                                id="query"
                                onChange={(e) => setColumnFilters((prev) => prev.map(filter => ({ ...filter, value: e.target.value })))}
                                className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter query"
                            />
                        </div>
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
                                                {editRowId === row.original.Id && (cell.column.id !== 'Id' && cell.column.id !== 'id') ? (
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
                                        {header.id !== 'Id' && header.id !== 'id' ? (
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
            <Toaster />
        </DashboardLayout>
    );
};

export default DataTable;

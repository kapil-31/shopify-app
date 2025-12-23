import {
    Page,
    Card,
    DataTable,
    Pagination,
    ChoiceList,
    Filters,
    Badge,
    Spinner,
} from "@shopify/polaris";
import { useEffect, useState } from "react";
import { useCallback } from "react";
import { useAppBridge } from "@shopify/app-bridge-react";
import { useAuthenticatedFetch } from "../hooks/useAuthenticatedFetch";

export default function Products() {
    const [products, setProducts] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [filterStatus, setFilterStatus] = useState([]);
    const [queryValue, setQueryValue] = useState("");
    const shopify = useAppBridge();
    const [isLoading, setIslLoading] = useState(true);
    const authFetch = useAuthenticatedFetch();

    const fetchProducts = (search, status, p = 1) => {
        setIslLoading(true);
        authFetch(`/api/products?page=${p}&search=${search}&status=${status}`)
            .then((res) => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                return res.json();
            })
            .then((res) => {
                setProducts(res.data);
                setMeta(res.meta);
            })
            .finally(() => {
                setIslLoading(false);
            });
    };
    const handleFilterStatusChange = useCallback(
        (value) => setFilterStatus(value),
        []
    );

    const handleFilterStatusRemove = useCallback(() => setFilterStatus([]), []);

    const handleAllQueryRemove = useCallback(
        () => (setFilterStatus([]), setQueryValue("")),
        []
    );

    useEffect(() => {
        fetchProducts(queryValue, filterStatus, page);
    }, [page, queryValue, filterStatus]);

    const filters = [
        {
            key: "status",
            label: "Status",
            filter: (
                <ChoiceList
                    title="Active"
                    titleHidden
                    choices={[
                        { label: "Active", value: "active" },
                        { label: "Draft", value: "draft" },
                        { label: "Archived", value: "archived" },
                    ]}
                    selected={filterStatus || []}
                    onChange={handleFilterStatusChange}
                />
            ),
        },
    ];

    const handleFiltersQueryChange = (value) => {
        setQueryValue(value);
    };
    const appliedFilters = [];

    if (!isEmpty(filterStatus)) {
        const key = "status";
        appliedFilters.push({
            key,
            label: `${
                filterStatus[0].charAt(0).toUpperCase() +
                filterStatus[0].slice(1)
            }`,
            onRemove: handleFilterStatusRemove,
        });
    }

    const rows = products.map((p) => [
        p.title,
        <Badge key={p.id} tone={statusTone(p.status)}>
            {p.status.charAt(0).toUpperCase() + p.status.slice(1)}
        </Badge>,
    ]);
    return (
        <Page
            title="Products"
            fullWidth
            primaryAction={{
                content: "Sync Products",
                onAction: () => {
                    setIslLoading(true);
                    authFetch("/api/sync/products")
                        .then(() => {
                            fetchProducts("", "", 1);
                            shopify.toast.show(
                                "Products Fetched successfully!",
                                { duration: 2000, isError: false }
                            );
                        })
                        .catch(() => {});
                },
            }}
        >
            <Card>
                <Filters
                    queryValue={queryValue}
                    queryPlaceholder="Search items"
                    filters={filters}
                    appliedFilters={appliedFilters}
                    onQueryChange={handleFiltersQueryChange}
                    onQueryClear={handleFiltersQueryChange}
                    onClearAll={handleAllQueryRemove}
                />
                <DataTable
                    columnContentTypes={["text", "text"]}
                    headings={["Title", "Status"]}
                    rows={rows}
                />
                {isLoading && (
                    <div
                        style={{
                            position: "absolute",
                            top: 0,
                            left: 0,
                            right: 0,
                            bottom: 0,
                            background: "rgba(255, 255, 255, 0.8)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                        }}
                    >
                        <Spinner size="large" />
                    </div>
                )}

                <Pagination
                    hasPrevious={page > 1}
                    hasNext={page < meta.last_page}
                    onPrevious={() => setPage(page - 1)}
                    onNext={() => setPage(page + 1)}
                    label={meta?.last_page &&  `page ${page} of ${meta.last_page}`}
                />
            </Card>
        </Page>
    );
}

function isEmpty(value) {
    if (Array.isArray(value)) {
        return value.length === 0;
    } else {
        return value === "" || value == null;
    }
}

const statusTone = (status) => {
    switch (status) {
        case "ACTIVE":
            return "success";
        case "DRAFT":
            return "attention";
        case "ARCHIVED":
            return "critical";
        default:
            return "subdued";
    }
};

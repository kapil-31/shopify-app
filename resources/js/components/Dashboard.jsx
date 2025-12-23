import {
  Page,
  Layout,
  Card,
  Text,
  InlineStack,
  BlockStack,
  Spinner,
} from "@shopify/polaris";
import { useEffect, useState } from "react";
import { useAuthenticatedFetch } from "../hooks/useAuthenticatedFetch";

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const authFetch = useAuthenticatedFetch(); 

  useEffect(() => {
    authFetch("/api/dashboard")
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP ${res.status}`);
        }
        return res.json();
      })
      .then((result) => {
        setData(result);
      })
      .catch((err) => {
        setError("Failed to load data. Please try again.");
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <Page title="Dashboard">
        <InlineStack align="center" blockAlign="center" gap="400">
          <Spinner size="large" />
          <Text variant="headingMd">Loading dashboard...</Text>
        </InlineStack>
      </Page>
    );
  }

  if (error) {
    return (
      <Page title="Dashboard">
        <Card>
          <Text tone="critical">{error}</Text>
        </Card>
      </Page>
    );
  }

  return (
    <Page fullWidth title="Dashboard" subtitle="Overview of your store data">
      <Layout>
        {/* Summary Cards */}
        <Layout.Section>
          <InlineStack gap="400" wrap={false}>

            {/* Total Products */}
            <Card padding="400">
              <div style={{width:300}}>
                <BlockStack gap="200">
                <Text as="h2" variant="headingSm">
                  Total Products
                </Text>
                <Text as="p" variant="headingXl">
                  {data?.total_products ?? 0}
                </Text>
              </BlockStack>
              </div>
            </Card>

            {/* Collections */}
            <Card padding="400">
              <div style={{width:300}}>
                <BlockStack gap="200">
                <Text as="h2" variant="headingSm">
                  Collections
                </Text>
                <Text as="p" variant="headingXl">
                  {data?.total_collections ?? 0}
                </Text>
                <Text as="p" tone="subdued">
                  {data?.collection_products ?? 0} products in collections
                </Text>
              </BlockStack>
              </div>
            </Card>

            {/* Last Sync */}
            <Card padding="400">
             <div style={{width:300}}>
               <BlockStack gap="200">
                <Text as="h2" variant="headingSm">
                  Last Sync
                </Text>
                <Text as="p" variant="bodyLg">
                  {data?.last_sync ?? "Never synced"}
                </Text>
              </BlockStack>
             </div>
            </Card>

          </InlineStack>
        </Layout.Section>
      </Layout>
    </Page>
  );
}
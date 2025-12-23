import "./bootstrap";
import "@shopify/polaris/build/esm/styles.css";
import "../css/app.css";

import { StrictMode, useState } from "react";
import { createRoot } from "react-dom/client";

import { AppProvider, Frame, Navigation } from "@shopify/polaris";
import { HomeIcon, ProductIcon } from "@shopify/polaris-icons";
import enTranslations from "@shopify/polaris/locales/en.json";

import { Provider as AppBridgeProvider } from "@shopify/app-bridge-react";

import Dashboard from "./components/Dashboard";
import Products from "./components/Products";


const appBridgeConfig = {
  apiKey: import.meta.env.VITE_SHOPIFY_API_KEY,
  host: new URLSearchParams(window.location.search).get("host"),
  forceRedirect: true,
};

function App() {
  const [activePage, setActivePage] = useState("dashboard");

  const navigationMarkup = (
    <Navigation>
      <Navigation.Section
        items={[
          {
            label: "Dashboard",
            icon: HomeIcon,
            selected: activePage === "dashboard",
            onClick: () => setActivePage("dashboard"),
          },
          {
            label: "Products",
            icon: ProductIcon,
            selected: activePage === "products",
            onClick: () => setActivePage("products"),
          },
        ]}
      />
    </Navigation>
  );

  return (
    <AppBridgeProvider config={appBridgeConfig}>
      <AppProvider i18n={enTranslations}>
        <Frame navigation={navigationMarkup}>
          {activePage === "dashboard" && <Dashboard />}
          {activePage === "products" && <Products />}
        </Frame>
      </AppProvider>
    </AppBridgeProvider>
  );
}


createRoot(document.getElementById("root")).render(
  <StrictMode>
    <App />
  </StrictMode>
);

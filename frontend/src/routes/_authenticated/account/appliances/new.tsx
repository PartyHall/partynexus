import ApplianceEditor from "@/components/account/appliances/appliance_editor";
import useTranslatedTitle from "@/hooks/useTranslatedTitle";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/_authenticated/account/appliances/new")({
  component: RouteComponent,
});

function RouteComponent() {
  useTranslatedTitle("account.my_appliances.editor.title_new");
  return <ApplianceEditor appliance={null} />;
}

import ApplianceEditor from '@/components/account/appliances/appliance_editor'
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/account/appliances/new')({
  component: RouteComponent,
})

function RouteComponent() {
  return <ApplianceEditor appliance={null} />
}

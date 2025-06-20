import { getAppliance } from '@/api/appliances';
import ApplianceEditor from '@/components/account/appliances/appliance_editor';
import { createFileRoute, useRouter } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/account/appliances/$id')({
  component: RouteComponent,
  loader: async ({ params }) => {
    const id = params.id;
    if (!id) {
      throw new Error('Appliance ID is required');
    }

    return await getAppliance(id);
  }
})

function RouteComponent() {
  const router = useRouter();

  return <ApplianceEditor
    appliance={Route.useLoaderData()}
    doInvalidateRoute={router.invalidate}
  />
}

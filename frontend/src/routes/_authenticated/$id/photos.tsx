import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/$id/photos')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/_authenticated/$id/photos"!</div>
}

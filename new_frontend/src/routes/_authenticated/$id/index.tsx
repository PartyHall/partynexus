import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/$id/')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/_authenticated/$id/"!</div>
}

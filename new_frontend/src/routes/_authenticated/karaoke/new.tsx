import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/karaoke/new')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/_authenticated/karaoke/new"!</div>
}

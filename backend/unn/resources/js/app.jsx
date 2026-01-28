import React from 'react'
import { createRoot } from 'react-dom/client'
import { InertiaApp } from '@inertiajs/inertia-react'

const el = document.getElementById('app')
if (el) {
  createRoot(el).render(
    <InertiaApp initialPage={JSON.parse(el.dataset.page)} />
  )
}

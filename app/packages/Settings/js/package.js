
const loadNovaSettings = (app) => {
  app.inertia('Nova.Settings', require('./pages/Settings').default)
  app.component('SettingsLoadingButton', require('./components/SettingsLoadingButton').default)
}

export { loadNovaSettings }
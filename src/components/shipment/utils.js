export function blockNonNumericInput(event) {
  if (event.key !== '.' && isNaN(Number(event.key))) {
    event.preventDefault()
  }
}

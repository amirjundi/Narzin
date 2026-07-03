import { screen } from "@testing-library/react";
import { renderWithProviders } from "./renderWithProviders";

test("test harness renders a component", () => {
  renderWithProviders(<p>hello narzin</p>);
  expect(screen.getByText("hello narzin")).toBeInTheDocument();
});

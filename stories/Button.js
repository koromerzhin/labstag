import "./button.scss";

import { html } from "lit";
import { styleMap } from "lit/directives/style-map.js";

/**
 * Primary UI component for user interaction
 */
export const Button = ({
  primary,
  backgroundColor = null,
  size,
  label,
  onClick,
}) => {
  const mode = primary
    ? "storybook-button--primary"
    : "storybook-button--secondary";

  return html`
    <button
      type="button"
      class=${[
        "storybook-button",
        `storybook-button--${size || "medium"}`,
        mode,
      ].join(" ")}
      style=${styleMap({ backgroundColor })}
      @click=${onClick}
    >
      ${label}
    </button>
  `;
};

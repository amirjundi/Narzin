import React from "react";
import { Link } from "react-router-dom";

export function linkTarget(link) {
  if (!link || !link.type) return null;
  if (link.type === "product") return { kind: "internal", to: `/product/${link.value}` };
  if (link.type === "category")
    return { kind: "internal", to: `/store?category_id=${link.value}` };
  if (link.type === "url") return { kind: "external", href: link.value };
  return null;
}

export function SmartLink({ link, className, children, ...rest }) {
  const target = linkTarget(link);
  if (!target) {
    return (
      <div className={className} {...rest}>
        {children}
      </div>
    );
  }
  if (target.kind === "external") {
    return (
      <a
        href={target.href}
        target="_blank"
        rel="noopener noreferrer"
        className={className}
        {...rest}
      >
        {children}
      </a>
    );
  }
  return (
    <Link to={target.to} className={className} {...rest}>
      {children}
    </Link>
  );
}

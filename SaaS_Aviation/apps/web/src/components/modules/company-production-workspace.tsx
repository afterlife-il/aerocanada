"use client";

import { FormEvent, useCallback, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { DetailPanel, EmptyState, ErrorState, LoadingState } from "@/components/ui/panels";
import { getDataSourceConfig } from "@/lib/data-source-mode";
import { persistentApi, type ApiCompany, type ApiCompany360 } from "@/lib/persistent-api";

const fieldClass = "h-9 w-full rounded-md border border-border bg-background px-3 text-sm outline-none focus:border-accent";

function values(form: HTMLFormElement): Record<string, unknown> {
  const data = new FormData(form);
  return Object.fromEntries(Array.from(data.entries()).map(([key, value]) => [key, key === "tags" || key === "roles" ? String(value).split(",").map((item) => item.trim()).filter(Boolean) : value]));
}

export function CompanyProductionWorkspace({ initialCompanies }: { initialCompanies: ApiCompany[] }) {
  const config = useMemo(() => getDataSourceConfig(), []);
  const persistent = config.mode === "persistent-api";
  const [companies, setCompanies] = useState(initialCompanies);
  const [selected, setSelected] = useState<ApiCompany360 | null>(null);
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState("all");
  const [sort, setSort] = useState("name");
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(null);

  const loadCompanies = useCallback(async () => {
    if (!persistent) return;
    setLoading(true); setError(null);
    try {
      const params = new URLSearchParams({ q: query, status, sort, page: String(page), pageSize: "10" });
      const result = await persistentApi.searchCompanies(params, config);
      setCompanies(result.rows); setTotalPages(result.pagination.totalPages);
    } catch (cause) { setError(cause instanceof Error ? cause.message : "Unable to load companies."); }
    finally { setLoading(false); }
  }, [config, page, persistent, query, sort, status]);

  useEffect(() => { void loadCompanies(); }, [loadCompanies]);

  async function openCompany(id: string) {
    if (!persistent) return;
    setLoading(true); setError(null);
    try { setSelected(await persistentApi.getCompany360(id, config)); }
    catch (cause) { setError(cause instanceof Error ? cause.message : "Unable to load Company 360."); }
    finally { setLoading(false); }
  }

  async function createCompany(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    try { await persistentApi.createCompany(values(event.currentTarget), config); event.currentTarget.reset(); setNotice("Company created."); await loadCompanies(); }
    catch (cause) { setError(cause instanceof Error ? cause.message : "Company creation failed."); }
  }

  async function updateCompany(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); if (!selected) return;
    try { await persistentApi.updateCompany(selected.company.id, values(event.currentTarget), config); setNotice("Company updated."); await openCompany(selected.company.id); await loadCompanies(); }
    catch (cause) { setError(cause instanceof Error ? cause.message : "Company update failed."); }
  }

  async function createContact(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); if (!selected) return;
    try { await persistentApi.createContact(selected.company.id, values(event.currentTarget), config); event.currentTarget.reset(); await openCompany(selected.company.id); }
    catch (cause) { setError(cause instanceof Error ? cause.message : "Contact creation failed."); }
  }

  async function editContact(id: string, firstName: string, lastName: string) {
    const jobTitle = window.prompt("Position/title", selected?.contacts.find((contact) => contact.id === id)?.jobTitle ?? "");
    if (jobTitle === null) return;
    await persistentApi.updateContact(id, { firstName, lastName, jobTitle }, config); if (selected) await openCompany(selected.company.id);
  }

  async function createAddress(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); if (!selected) return;
    const input = values(event.currentTarget); input.isPrimary = new FormData(event.currentTarget).get("isPrimary") === "on";
    try { await persistentApi.createAddress(selected.company.id, input, config); event.currentTarget.reset(); await openCompany(selected.company.id); }
    catch (cause) { setError(cause instanceof Error ? cause.message : "Address creation failed."); }
  }

  if (!persistent) {
    const filtered = companies.filter((company) => [company.name, company.code, company.country, ...company.tags].some((value) => String(value ?? "").toLowerCase().includes(query.toLowerCase())));
    return <>
      <div className="mb-3 rounded-md border border-border bg-panel-muted px-3 py-2 text-sm text-muted">Public/sample-static mode is read-only. Enable <code>persistent-api</code> locally to use verified PostgreSQL CRUD; no fallback occurs.</div>
      <input className={fieldClass} value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search company, code, country, or tag" aria-label="Search companies" />
      <div className="mt-3 grid gap-2">{filtered.map((company) => <Link className="rounded-md border border-border bg-panel p-3 font-semibold hover:bg-panel-muted" href={`/companies/${company.id}`} key={company.id}>{company.name}<span className="ml-2 text-xs font-normal text-muted">{company.code ?? company.country ?? ""}</span></Link>)}</div>
    </>;
  }

  return <div className="space-y-4">
    {error ? <ErrorState title="Company module error" detail={error} /> : null}
    {notice ? <div className="rounded-md border border-border bg-panel-muted px-3 py-2 text-sm">{notice}</div> : null}
    <div className="grid gap-2 lg:grid-cols-[1fr_150px_150px_auto]">
      <input className={fieldClass} value={query} onChange={(event) => { setPage(1); setQuery(event.target.value); }} placeholder="Fast search: name, codes, VAT, email, phone, tag" />
      <select className={fieldClass} value={status} onChange={(event) => { setPage(1); setStatus(event.target.value); }}><option value="all">All status</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="blocked">Blocked</option></select>
      <select className={fieldClass} value={sort} onChange={(event) => setSort(event.target.value)}><option value="name">Name</option><option value="code">Code</option><option value="updatedAt">Last activity</option></select>
      <Button onClick={() => void loadCompanies()} variant="primary">Refresh</Button>
    </div>
    {loading ? <LoadingState /> : <div className="grid gap-2">{companies.map((company) => <button className="flex items-center justify-between rounded-md border border-border bg-panel p-3 text-left hover:bg-panel-muted" key={company.id} onClick={() => void openCompany(company.id)}><span className="font-semibold">{company.name}</span><span className="text-xs text-muted">{company.icaoCode ?? company.iataCode ?? company.code ?? company.status}</span></button>)}</div>}
    <div className="flex items-center justify-between text-sm"><Button disabled={page <= 1} onClick={() => setPage((value) => value - 1)}>Previous</Button><span>Page {page} of {totalPages}</span><Button disabled={page >= totalPages} onClick={() => setPage((value) => value + 1)}>Next</Button></div>
    <DetailPanel title="Create Company"><form className="grid gap-2 md:grid-cols-3" onSubmit={createCompany}><input required name="name" placeholder="Company name" className={fieldClass}/><input name="legalName" placeholder="Legal name" className={fieldClass}/><input name="code" placeholder="Code" className={fieldClass}/><input name="icaoCode" placeholder="ICAO (4)" maxLength={4} className={fieldClass}/><input name="iataCode" placeholder="IATA (3)" maxLength={3} className={fieldClass}/><input name="vatNumber" placeholder="VAT number" className={fieldClass}/><input name="country" placeholder="Country" className={fieldClass}/><input name="email" type="email" placeholder="Email" className={fieldClass}/><input name="phone" placeholder="Phone" className={fieldClass}/><input name="website" type="url" placeholder="Website" className={fieldClass}/><input name="roles" defaultValue="customer" placeholder="Roles, comma separated" className={fieldClass}/><input name="tags" placeholder="Tags, comma separated" className={fieldClass}/><textarea name="notes" placeholder="Notes" className="min-h-20 rounded-md border border-border bg-background p-3 text-sm md:col-span-3"/><Button variant="primary" type="submit">Create Company</Button></form></DetailPanel>
    {selected ? <CompanyDetail value={selected} fieldClass={fieldClass} onUpdate={updateCompany} onCreateContact={createContact} onEditContact={editContact} onDeleteContact={async (id) => { await persistentApi.deleteContact(id, config); await openCompany(selected.company.id); }} onCreateAddress={createAddress} onDeleteAddress={async (id) => { await persistentApi.deleteAddress(id, config); await openCompany(selected.company.id); }} onDeleteCompany={async () => { if (!window.confirm("Delete this company? Linked stock prevents deletion.")) return; await persistentApi.deleteCompany(selected.company.id, config); setSelected(null); await loadCompanies(); }} /> : <EmptyState title="Select a company" detail="Open a PostgreSQL-backed Company 360 record to manage identity, contacts, addresses, inventory, documents, and activity." />}
  </div>;
}

function CompanyDetail({ value, fieldClass, onUpdate, onCreateContact, onEditContact, onDeleteContact, onCreateAddress, onDeleteAddress, onDeleteCompany }: { value: ApiCompany360; fieldClass: string; onUpdate: (event: FormEvent<HTMLFormElement>) => void; onCreateContact: (event: FormEvent<HTMLFormElement>) => void; onEditContact: (id: string, firstName: string, lastName: string) => void; onDeleteContact: (id: string) => void; onCreateAddress: (event: FormEvent<HTMLFormElement>) => void; onDeleteAddress: (id: string) => void; onDeleteCompany: () => void; }) {
  const company = value.company;
  return <div className="space-y-4">
    <DetailPanel title={`Company 360 · ${company.name}`} actions={<div className="flex gap-2"><Button onClick={() => document.getElementById("company-edit")?.scrollIntoView()}>Edit Company</Button><Button variant="danger" onClick={onDeleteCompany}>Delete</Button></div>}><div className="grid gap-2 text-sm md:grid-cols-4"><span>Legal: {company.legalName ?? "-"}</span><span>Code: {company.code ?? "-"}</span><span>ICAO: {company.icaoCode ?? "-"}</span><span>IATA: {company.iataCode ?? "-"}</span><span>VAT: {company.vatNumber ?? "-"}</span><span>Country: {company.country ?? "-"}</span><span>Website: {company.website ?? "-"}</span><span>Tags: {company.tags.join(", ") || "-"}</span></div></DetailPanel>
    <DetailPanel id="company-edit" title="Edit Company"><form className="grid gap-2 md:grid-cols-3" onSubmit={onUpdate}><input required name="name" defaultValue={company.name} className={fieldClass}/><input name="legalName" defaultValue={company.legalName} placeholder="Legal name" className={fieldClass}/><input name="code" defaultValue={company.code} placeholder="Code" className={fieldClass}/><input name="icaoCode" defaultValue={company.icaoCode} placeholder="ICAO" maxLength={4} className={fieldClass}/><input name="iataCode" defaultValue={company.iataCode} placeholder="IATA" maxLength={3} className={fieldClass}/><input name="vatNumber" defaultValue={company.vatNumber} placeholder="VAT" className={fieldClass}/><input name="country" defaultValue={company.country} placeholder="Country" className={fieldClass}/><input name="email" defaultValue={company.email} type="email" placeholder="Email" className={fieldClass}/><input name="phone" defaultValue={company.phone} placeholder="Phone" className={fieldClass}/><input name="website" defaultValue={company.website} type="url" placeholder="Website" className={fieldClass}/><input name="roles" defaultValue={company.roles.join(",")} className={fieldClass}/><input name="tags" defaultValue={company.tags.join(",")} className={fieldClass}/><textarea name="notes" defaultValue={company.notes} className="min-h-20 rounded-md border border-border bg-background p-3 text-sm md:col-span-3"/><Button variant="primary" type="submit">Save Company</Button></form></DetailPanel>
    <div className="grid gap-4 xl:grid-cols-2"><DetailPanel title="Contacts"><form className="mb-3 grid gap-2 md:grid-cols-2" onSubmit={onCreateContact}><input required name="firstName" placeholder="First name" className={fieldClass}/><input required name="lastName" placeholder="Last name" className={fieldClass}/><input name="jobTitle" placeholder="Position" className={fieldClass}/><input name="email" type="email" placeholder="Email" className={fieldClass}/><input name="phone" placeholder="Phone" className={fieldClass}/><input name="mobile" placeholder="Mobile" className={fieldClass}/><Button variant="primary" type="submit">Create Contact</Button></form>{value.contacts.map((contact) => <div className="flex items-center justify-between border-t border-border py-2 text-sm" key={contact.id}><span>{contact.firstName} {contact.lastName} · {contact.jobTitle ?? contact.email ?? "Contact"}</span><span className="flex gap-2"><Button onClick={() => onEditContact(contact.id, contact.firstName, contact.lastName)}>Edit</Button><Button variant="danger" onClick={() => onDeleteContact(contact.id)}>Delete</Button></span></div>)}</DetailPanel>
    <DetailPanel title="Addresses"><form className="mb-3 grid gap-2 md:grid-cols-2" onSubmit={onCreateAddress}><input required name="label" placeholder="Label" className={fieldClass}/><input required name="addressLine1" placeholder="Address" className={fieldClass}/><input name="city" placeholder="City" className={fieldClass}/><input required name="country" placeholder="Country" className={fieldClass}/><label className="text-sm"><input name="isPrimary" type="checkbox"/> Primary</label><Button variant="primary" type="submit">Add Address</Button></form>{value.addresses.map((address) => <div className="flex items-center justify-between border-t border-border py-2 text-sm" key={address.id}><span>{address.label}: {address.addressLine1}, {address.city} {address.country}{address.isPrimary ? " · Primary" : ""}</span><Button variant="danger" onClick={() => onDeleteAddress(address.id)}>Delete</Button></div>)}</DetailPanel></div>
    <div className="grid gap-4 xl:grid-cols-3"><DetailPanel title="Company Inventory"><div className="mb-2 text-sm">{value.inventory.length} PostgreSQL stock record(s)</div>{value.inventory.map((stock) => <div className="border-t border-border py-2 text-sm" key={stock.id}>Part {stock.partId} · Qty {stock.quantity}</div>)}<Link className="mt-3 inline-block font-semibold text-accent" href="/company-inventory">Open Inventory</Link></DetailPanel><DetailPanel title="Company Documents"><div className="mb-2 text-sm">{value.documents.documents.length} linked document(s)</div>{value.documents.documents.map((document) => <div className="border-t border-border py-2 text-sm" key={document.id}>{document.title} · {document.status}</div>)}<Link className="mt-3 inline-block font-semibold text-accent" href="/documents">Upload Document</Link></DetailPanel><DetailPanel title="Activity Timeline">{value.activity.map((activity) => <div className="border-t border-border py-2 text-sm" key={activity.id}><div className="font-semibold">{activity.summary}</div><div className="text-xs text-muted">{activity.category} · {activity.occurredAt}</div></div>)}</DetailPanel></div>
    <DetailPanel title="Workflow Boundaries"><div className="grid gap-2 md:grid-cols-5">{value.workflowBoundaries.map((boundary) => <button className="rounded-md border border-border bg-panel-muted p-3 text-left text-sm font-semibold" key={boundary.category} onClick={() => window.alert(`${boundary.category} remains a clean module boundary; Company context ${boundary.companyId} is ready.`)}>{boundary.category === "rfq" ? "Create RFQ" : `Open ${boundary.category}`}</button>)}</div></DetailPanel>
  </div>;
}

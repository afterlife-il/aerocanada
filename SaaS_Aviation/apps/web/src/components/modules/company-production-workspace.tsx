"use client";

import { FormEvent, useCallback, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { DetailPanel, EmptyState, ErrorState, LoadingState } from "@/components/ui/panels";
import { getDataSourceConfig, initialRecordsForMode } from "@/lib/data-source-mode";
import { normalizeFormData } from "@/lib/form-normalization";
import { persistentApi, PersistentApiError, type ApiCompany, type ApiCompany360, type ApiCompanyAddress, type ApiCompanyNote, type ApiContact } from "@/lib/persistent-api";

const fieldClass = "h-9 w-full rounded-md border border-border bg-background px-3 text-sm outline-none focus:border-accent";
const areaClass = "min-h-20 rounded-md border border-border bg-background p-3 text-sm md:col-span-2";

function formValues(form: HTMLFormElement, arrays: string[] = [], booleans: string[] = []) {
  return normalizeFormData(new FormData(form), { arrayFields: arrays, booleanFields: booleans });
}

export function CompanyProductionWorkspace({ initialCompanies }: { initialCompanies: ApiCompany[] }) {
  const config = useMemo(() => getDataSourceConfig(), []);
  const persistent = config.mode === "persistent-api";
  const [companies, setCompanies] = useState<ApiCompany[]>(initialRecordsForMode(config, initialCompanies));
  const [selected, setSelected] = useState<ApiCompany360 | null>(null);
  const [editingContact, setEditingContact] = useState<ApiContact | null>(null);
  const [editingAddress, setEditingAddress] = useState<ApiCompanyAddress | null>(null);
  const [editingNote, setEditingNote] = useState<ApiCompanyNote | null>(null);
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState("all");
  const [sort, setSort] = useState("name");
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(null);
  const [needsSignIn, setNeedsSignIn] = useState(false);

  const fail = (cause: unknown, fallback: string) => setError(cause instanceof Error ? cause.message : fallback);
  const loadCompanies = useCallback(async () => {
    if (!persistent) return;
    setLoading(true); setError(null);
    try {
      const params = new URLSearchParams({ q: query, status, sort, page: String(page), pageSize: "10" });
      const result = await persistentApi.searchCompanies(params, config);
      setCompanies(result.rows); setTotalPages(result.pagination.totalPages); setNeedsSignIn(false);
    } catch (cause) {
      setCompanies([]); setSelected(null);
      setNeedsSignIn(cause instanceof PersistentApiError && cause.status === 401);
      fail(cause, "Unable to load companies.");
    }
    finally { setLoading(false); }
  }, [config, page, persistent, query, sort, status]);

  useEffect(() => { void loadCompanies(); }, [loadCompanies]);

  async function openCompany(id: string) {
    setLoading(true); setError(null);
    try { setSelected(await persistentApi.getCompany360(id, config)); }
    catch (cause) { fail(cause, "Unable to load Company 360."); }
    finally { setLoading(false); }
  }

  async function submit(event: FormEvent<HTMLFormElement>, action: (input: Record<string, unknown>) => Promise<unknown>, success: string, arrays: string[] = [], booleans: string[] = []) {
    event.preventDefault(); setSaving(true); setError(null); setNotice(null);
    try { await action(formValues(event.currentTarget, arrays, booleans)); setNotice(success); return true; }
    catch (cause) { fail(cause, success.replace(/\.$/, "") + " failed."); return false; }
    finally { setSaving(false); }
  }

  if (!persistent) {
    const filtered = companies.filter((company) => [company.name, company.code, company.country, ...company.tags].some((value) => String(value ?? "").toLowerCase().includes(query.toLowerCase())));
    return <>
      <div className="mb-3 rounded-md border border-border bg-panel-muted px-3 py-2 text-sm text-muted">Public/sample-static mode is read-only. Enable <code>persistent-api</code> locally to use PostgreSQL CRUD; no fallback occurs.</div>
      <input className={fieldClass} value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search company, code, country, or tag" aria-label="Search companies" />
      <div className="mt-3 grid gap-2">{filtered.map((company) => <Link className="rounded-md border border-border bg-panel p-3 font-semibold hover:bg-panel-muted" href={`/companies/${company.id}`} key={company.id}>{company.name}</Link>)}</div>
    </>;
  }

  return <div className="space-y-4">
    {error ? <ErrorState title={needsSignIn ? "Sign-in required" : "Company module error"} detail={error} actions={<><Button onClick={() => void loadCompanies()}>Retry</Button>{needsSignIn ? <Link className="rounded-md bg-accent px-3 py-2 text-sm font-semibold text-white" href="/login/">Sign in</Link> : null}</>} /> : null}
    {notice ? <div role="status" className="rounded-md border border-border bg-panel-muted px-3 py-2 text-sm">{notice}</div> : null}
    <div className="grid gap-2 lg:grid-cols-[1fr_150px_150px_auto]">
      <input className={fieldClass} value={query} onChange={(event) => { setPage(1); setQuery(event.target.value); }} placeholder="Fast search: name, codes, VAT, email, phone, tag" />
      <select className={fieldClass} value={status} onChange={(event) => { setPage(1); setStatus(event.target.value); }}><option value="all">All status</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="blocked">Blocked</option></select>
      <select className={fieldClass} value={sort} onChange={(event) => setSort(event.target.value)}><option value="name">Name</option><option value="code">Code</option><option value="updatedAt">Last activity</option></select>
      <Button onClick={() => void loadCompanies()} variant="primary">Refresh</Button>
    </div>
    {loading ? <LoadingState /> : companies.length ? <div className="grid gap-2">{companies.map((company) => <button className="flex items-center justify-between rounded-md border border-border bg-panel p-3 text-left hover:bg-panel-muted" key={company.id} onClick={() => void openCompany(company.id)}><span className="font-semibold">{company.name}</span><span className="text-xs text-muted">{company.icaoCode ?? company.iataCode ?? company.code ?? company.status}</span></button>)}</div> : !error ? <EmptyState title="No companies found" detail="No PostgreSQL company matches the current filters." /> : null}
    <div className="flex items-center justify-between text-sm"><Button disabled={page <= 1} onClick={() => setPage((value) => value - 1)}>Previous</Button><span>Page {page} of {totalPages}</span><Button disabled={page >= totalPages} onClick={() => setPage((value) => value + 1)}>Next</Button></div>
    <DetailPanel title="Create Company"><CompanyForm submitLabel="Create Company" busy={saving} onSubmit={async (event) => { const form = event.currentTarget; if (!await submit(event, (input) => persistentApi.createCompany(input, config), "Company created.", ["roles", "tags"])) return; form.reset(); await loadCompanies(); }} /></DetailPanel>
    {selected ? <div className="space-y-4">
      <DetailPanel title={`Company 360 - ${selected.company.name}`} actions={<Button variant="danger" onClick={async () => { if (!window.confirm("Delete this company? Linked stock prevents deletion.")) return; await persistentApi.deleteCompany(selected.company.id, config); setSelected(null); await loadCompanies(); }}>Delete</Button>}>
        <div className="grid gap-2 text-sm md:grid-cols-4"><span>Legal: {selected.company.legalName ?? "-"}</span><span>Code: {selected.company.code ?? "-"}</span><span>ICAO: {selected.company.icaoCode ?? "-"}</span><span>IATA: {selected.company.iataCode ?? "-"}</span><span>VAT: {selected.company.vatNumber ?? "-"}</span><span>Country: {selected.company.country ?? "-"}</span><span>Website: {selected.company.website ?? "-"}</span><span>Tags: {selected.company.tags.join(", ") || "-"}</span></div>
      </DetailPanel>
      <DetailPanel title="Edit Company"><CompanyForm company={selected.company} submitLabel="Save Company" busy={saving} onSubmit={async (event) => { if (!await submit(event, (input) => persistentApi.updateCompany(selected.company.id, input, config), "Company updated.", ["roles", "tags"])) return; await openCompany(selected.company.id); await loadCompanies(); }} /></DetailPanel>
      <div className="grid gap-4 xl:grid-cols-2">
        <DetailPanel title="Contacts"><ContactForm busy={saving} submitLabel="Create Contact" onSubmit={async (event) => { const form = event.currentTarget; if (!await submit(event, (input) => persistentApi.createContact(selected.company.id, input, config), "Contact created.")) return; form.reset(); await openCompany(selected.company.id); }} />{selected.contacts.map((contact) => <div className="flex items-center justify-between border-t border-border py-2 text-sm" key={contact.id}><span>{contact.firstName} {contact.lastName} - {contact.jobTitle ?? contact.email ?? "Contact"}</span><span className="flex gap-2"><Button onClick={() => setEditingContact(contact)}>Edit</Button><Button variant="danger" onClick={async () => { if (!window.confirm("Delete this contact?")) return; await persistentApi.deleteContact(contact.id, config); await openCompany(selected.company.id); }}>Delete</Button></span></div>)}</DetailPanel>
        <DetailPanel title="Addresses"><AddressForm busy={saving} submitLabel="Add Address" onSubmit={async (event) => { const form = event.currentTarget; if (!await submit(event, (input) => persistentApi.createAddress(selected.company.id, input, config), "Address created.", [], ["isPrimary"])) return; form.reset(); await openCompany(selected.company.id); }} />{selected.addresses.map((address) => <div className="flex items-center justify-between border-t border-border py-2 text-sm" key={address.id}><span>{address.label}: {address.addressLine1}, {address.city} {address.country}{address.isPrimary ? " - Primary" : ""}</span><span className="flex gap-2"><Button onClick={() => setEditingAddress(address)}>Edit</Button><Button variant="danger" onClick={async () => { if (!window.confirm("Delete this address?")) return; await persistentApi.deleteAddress(address.id, config); await openCompany(selected.company.id); }}>Delete</Button></span></div>)}</DetailPanel>
      </div>
      {editingContact ? <DetailPanel title={`Edit Contact - ${editingContact.firstName} ${editingContact.lastName}`} actions={<Button onClick={() => setEditingContact(null)}>Cancel</Button>}><ContactForm contact={editingContact} busy={saving} submitLabel="Save Contact" onSubmit={async (event) => { if (!await submit(event, (input) => persistentApi.updateContact(editingContact.id, input, config), "Contact updated.")) return; setEditingContact(null); await openCompany(selected.company.id); }} /></DetailPanel> : null}
      {editingAddress ? <DetailPanel title={`Edit Address - ${editingAddress.label}`} actions={<Button onClick={() => setEditingAddress(null)}>Cancel</Button>}><AddressForm address={editingAddress} busy={saving} submitLabel="Save Address" onSubmit={async (event) => { if (!await submit(event, (input) => persistentApi.updateAddress(editingAddress.id, input, config), "Address updated.", [], ["isPrimary"])) return; setEditingAddress(null); await openCompany(selected.company.id); }} /></DetailPanel> : null}
      <DetailPanel title="Company Notes"><NoteForm busy={saving} submitLabel="Add Note" onSubmit={async (event) => { const form = event.currentTarget; if (!await submit(event, (input) => persistentApi.createCompanyNote(selected.company.id, input, config), "Note created.", [], ["pinned"])) return; form.reset(); await openCompany(selected.company.id); }} />{selected.notes.length ? selected.notes.map((note) => <article className="border-t border-border py-3 text-sm" key={note.id}><div className="flex items-start justify-between gap-3"><div><div className="whitespace-pre-wrap">{note.body}</div><div className="mt-1 text-xs text-muted">{note.pinned ? "Pinned - " : ""}Updated {note.updatedAt}</div></div><span className="flex gap-2"><Button onClick={() => setEditingNote(note)}>Edit</Button><Button variant="danger" onClick={async () => { if (!window.confirm("Delete this note?")) return; await persistentApi.deleteCompanyNote(note.id, config); await openCompany(selected.company.id); }}>Delete</Button></span></div></article>) : <p className="text-sm text-muted">No company notes recorded.</p>}</DetailPanel>
      {editingNote ? <DetailPanel title="Edit Company Note" actions={<Button onClick={() => setEditingNote(null)}>Cancel</Button>}><NoteForm note={editingNote} busy={saving} submitLabel="Save Note" onSubmit={async (event) => { if (!await submit(event, (input) => persistentApi.updateCompanyNote(editingNote.id, input, config), "Note updated.", [], ["pinned"])) return; setEditingNote(null); await openCompany(selected.company.id); }} /></DetailPanel> : null}
      <div className="grid gap-4 xl:grid-cols-3"><DetailPanel title="Company Inventory"><div className="mb-2 text-sm">{selected.inventory.length} PostgreSQL stock record(s)</div>{selected.inventory.map((stock) => <div className="border-t border-border py-2 text-sm" key={stock.id}>Part {stock.partId} - Qty {stock.quantity}</div>)}</DetailPanel><DetailPanel title="Documents Workflow Boundary"><p className="text-sm text-muted">Persistent Company mode does not display fixture documents. Durable Company document links and upload storage belong to the future Documents module.</p><Link className="mt-3 inline-block font-semibold text-accent" href="/documents">Open Documents foundation</Link></DetailPanel><DetailPanel title="Activity Timeline">{selected.activity.map((activity) => <div className="border-t border-border py-2 text-sm" key={activity.id}><div className="font-semibold">{activity.summary}</div><div className="text-xs text-muted">{activity.category} - {activity.occurredAt}</div></div>)}</DetailPanel></div>
      <DetailPanel title="Commercial Workflow Boundaries"><div className="grid gap-3">{selected.workflowBoundaries.map((boundary) => <div className="rounded-md border border-border bg-panel-muted p-3" key={boundary.category}><div className="flex justify-between"><strong>{boundary.category}</strong><span className="text-xs uppercase text-muted">No persistence</span></div><div className="mt-2 grid gap-2 text-xs md:grid-cols-3"><span>Future owner: {boundary.futureOwner}</span><span>Required: {boundary.requiredData.join(", ")}</span><span>Checks: {boundary.contextChecks.join(", ")}</span></div></div>)}</div></DetailPanel>
    </div> : <EmptyState title="Select a company" detail="Open a PostgreSQL-backed record to manage Company identity, Contacts, Addresses, inventory, and activity." />}
  </div>;
}

function CompanyForm({ company, onSubmit, submitLabel, busy }: { company?: ApiCompany; onSubmit: (event: FormEvent<HTMLFormElement>) => void; submitLabel: string; busy: boolean }) {
  return <form className="grid gap-2 md:grid-cols-3" onSubmit={onSubmit}><input required name="name" defaultValue={company?.name} placeholder="Company name" className={fieldClass}/><input name="legalName" defaultValue={company?.legalName} placeholder="Legal name" className={fieldClass}/><input name="code" defaultValue={company?.code} placeholder="Code" className={fieldClass}/><input name="icaoCode" defaultValue={company?.icaoCode} placeholder="ICAO (4)" maxLength={4} className={fieldClass}/><input name="iataCode" defaultValue={company?.iataCode} placeholder="IATA (3)" maxLength={3} className={fieldClass}/><input name="vatNumber" defaultValue={company?.vatNumber} placeholder="VAT number" className={fieldClass}/><input name="country" defaultValue={company?.country} placeholder="Country" className={fieldClass}/><input name="email" defaultValue={company?.email} type="email" placeholder="Email" className={fieldClass}/><input name="phone" defaultValue={company?.phone} placeholder="Phone" className={fieldClass}/><input name="website" defaultValue={company?.website} type="url" placeholder="Website" className={fieldClass}/><input name="roles" defaultValue={company?.roles.join(",") ?? "customer"} placeholder="Roles, comma separated" className={fieldClass}/><input name="tags" defaultValue={company?.tags.join(",")} placeholder="Tags, comma separated" className={fieldClass}/><textarea name="notes" defaultValue={company?.notes} placeholder="Notes" className={`${areaClass} md:col-span-3`}/><Button disabled={busy} variant="primary" type="submit">{submitLabel}</Button></form>;
}

function ContactForm({ contact, onSubmit, submitLabel, busy }: { contact?: ApiContact; onSubmit: (event: FormEvent<HTMLFormElement>) => void; submitLabel: string; busy: boolean }) {
  return <form className="mb-3 grid gap-2 md:grid-cols-2" onSubmit={onSubmit}><input required name="firstName" defaultValue={contact?.firstName} placeholder="First name" className={fieldClass}/><input required name="lastName" defaultValue={contact?.lastName} placeholder="Last name" className={fieldClass}/><input name="jobTitle" defaultValue={contact?.jobTitle} placeholder="Position" className={fieldClass}/><input name="email" defaultValue={contact?.email} type="email" placeholder="Email" className={fieldClass}/><input name="phone" defaultValue={contact?.phone} placeholder="Phone" className={fieldClass}/><input name="mobile" defaultValue={contact?.mobile} placeholder="Mobile" className={fieldClass}/><select name="status" defaultValue={contact?.status ?? "active"} className={fieldClass}><option value="active">Active</option><option value="inactive">Inactive</option></select><textarea name="notes" defaultValue={contact?.notes} placeholder="Notes" className={areaClass}/><div className="flex gap-2"><Button disabled={busy} variant="primary" type="submit">{submitLabel}</Button></div></form>;
}

function AddressForm({ address, onSubmit, submitLabel, busy }: { address?: ApiCompanyAddress; onSubmit: (event: FormEvent<HTMLFormElement>) => void; submitLabel: string; busy: boolean }) {
  return <form className="mb-3 grid gap-2 md:grid-cols-2" onSubmit={onSubmit}><input required name="label" defaultValue={address?.label} placeholder="Label" className={fieldClass}/><input required name="addressLine1" defaultValue={address?.addressLine1} placeholder="Address line 1" className={fieldClass}/><input name="addressLine2" defaultValue={address?.addressLine2} placeholder="Address line 2" className={fieldClass}/><input name="city" defaultValue={address?.city} placeholder="City" className={fieldClass}/><input name="state" defaultValue={address?.state} placeholder="State/region" className={fieldClass}/><input name="postalCode" defaultValue={address?.postalCode} placeholder="Postal code" className={fieldClass}/><input required name="country" defaultValue={address?.country} placeholder="Country" className={fieldClass}/><label className="flex items-center gap-2 text-sm"><input name="isPrimary" type="checkbox" defaultChecked={address?.isPrimary}/> Primary address</label><Button disabled={busy} variant="primary" type="submit">{submitLabel}</Button></form>;
}

function NoteForm({ note, onSubmit, submitLabel, busy }: { note?: ApiCompanyNote; onSubmit: (event: FormEvent<HTMLFormElement>) => void; submitLabel: string; busy: boolean }) {
  return <form className="mb-3 grid gap-2" onSubmit={onSubmit}><textarea required maxLength={5000} name="body" defaultValue={note?.body} placeholder="Operational note, customer preference, follow-up, or risk context" className={`${areaClass} md:col-span-1`}/><label className="flex items-center gap-2 text-sm"><input name="pinned" type="checkbox" defaultChecked={note?.pinned}/> Pin this note</label><Button disabled={busy} variant="primary" type="submit">{submitLabel}</Button></form>;
}

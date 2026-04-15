import { builderState } from '../lib/constants.js';
import { useEditorStore } from '../store/editorStore.js';

function getRevisionTypeLabel(type) {
    if (type === 'live_save') {
        return 'live save';
    }

    return type || 'сохранение';
}

export function HistoryPanel({ onRestoreRevision }) {
    const restoreBusyId = useEditorStore((state) => state.restoreBusyId);
    const sessionEvents = useEditorStore((state) => state.sessionEvents);
    const revisions = builderState.history?.items || [];

    return (
        <div className="ne-panel">
            <div className="ne-panel__header">
                <div>
                    <h2 className="ne-panel__title">История</h2>
                    <div className="ne-panel__subtitle">Серверные ревизии черновика layout и локальная история действий.</div>
                </div>
            </div>

            {revisions.length ? (
                <div className="ne-history">
                    {revisions.map((revision) => (
                        <div key={revision.id} className="ne-history__item">
                            <div className="ne-history__meta">
                                <div className="ne-history__title">#{revision.id} · {getRevisionTypeLabel(revision.revision_type)}</div>
                                <div className="ne-history__time">{revision.created_at || '—'}</div>
                            </div>

                            <button
                                className="ne-button ne-button--ghost"
                                type="button"
                                disabled={restoreBusyId === revision.id}
                                onClick={() => onRestoreRevision(revision.id)}
                            >
                                {restoreBusyId === revision.id ? 'Идет восстановление' : 'Восстановить'}
                            </button>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="ne-empty">Серверных ревизий пока нет. Ниже остается живая история действий в редакторе.</div>
            )}

            <div className="ne-history__section-title">Сессия</div>

            {sessionEvents.length ? (
                <div className="ne-history ne-history--session">
                    {sessionEvents.map((event) => (
                        <div key={event.id} className="ne-history__item">
                            <div className="ne-history__meta">
                                <div className="ne-history__title">{event.label}</div>
                                <div className="ne-history__time">{event.detail || event.createdAt}</div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="ne-empty">История сессии начнет заполняться после выбора узлов и действий в редакторе.</div>
            )}
        </div>
    );
}
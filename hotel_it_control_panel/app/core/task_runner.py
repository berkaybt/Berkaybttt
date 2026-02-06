from __future__ import annotations

from dataclasses import dataclass
from typing import Any, Callable

from PySide6.QtCore import QObject, QRunnable, QThreadPool, Signal, Slot


@dataclass
class Task:
    name: str
    fn: Callable[..., Any]
    args: tuple[Any, ...]
    kwargs: dict[str, Any]
    cancelled: bool = False


class TaskSignals(QObject):
    result = Signal(object)
    error = Signal(str)
    finished = Signal()


class TaskRunner(QRunnable):
    def __init__(self, task: Task) -> None:
        super().__init__()
        self.task = task
        self.signals = TaskSignals()

    @Slot()
    def run(self) -> None:
        if self.task.cancelled:
            self.signals.finished.emit()
            return
        try:
            result = self.task.fn(*self.task.args, **self.task.kwargs)
        except Exception as exc:  # noqa: BLE001
            self.signals.error.emit(str(exc))
        else:
            self.signals.result.emit(result)
        finally:
            self.signals.finished.emit()


class TaskRunnerPool:
    def __init__(self) -> None:
        self.pool = QThreadPool.globalInstance()
        self.tasks: list[Task] = []

    def start(self, task: Task, on_result=None, on_error=None, on_finished=None) -> None:  # type: ignore[no-untyped-def]
        self.tasks.append(task)
        runner = TaskRunner(task)
        if on_result:
            runner.signals.result.connect(on_result)
        if on_error:
            runner.signals.error.connect(on_error)
        if on_finished:
            runner.signals.finished.connect(on_finished)
        self.pool.start(runner)

    def cancel(self, task_name: str) -> None:
        for task in self.tasks:
            if task.name == task_name:
                task.cancelled = True

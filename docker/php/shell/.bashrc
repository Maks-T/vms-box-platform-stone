# If this is not an interactive shell, exit (don’t run the rest)
[ -z "$PS1" ] && return

# Source user aliases if the file exists
if [ -f ~/.aliases ]; then
    . ~/.aliases
fi

# Command history settings
export HISTCONTROL=ignoreboth:erasedups   # ignore duplicates and lines starting with space
export HISTSIZE=1000                      # number of commands kept in memory
export HISTFILESIZE=2000                  # number of commands kept in the history file

# Enable colored output for ls and grep if dircolors is available
if [ -x /usr/bin/dircolors ]; then
    # Use a custom dircolors file if it exists, otherwise use the system defaults
    test -r ~/.dircolors && eval "$(dircolors -b ~/.dircolors)" || eval "$(dircolors -b)"
    alias ls='ls --color=auto'
    alias grep='grep --color=auto'
fi

# Custom Laravel-style prompt
# User (pink) @ Host (pink) : Path (orange) $
export PS1='\[\033[38;5;198m\]\u\[\033[38;5;255m\]@\[\033[38;5;198m\]\h\[\033[38;5;255m\]:\[\033[38;5;208m\]\w\[\033[38;5;255m\]\$ \[\033[0m\]'